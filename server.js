'use strict'

var express = require('express')
var app = express()
var http = require('http').Server(app)
var io = require('socket.io')(http)

var Redis = require('ioredis')
var redis = new Redis()
var pubsub = new Redis()

var path = require('path')
app.use(express.static(path.join(__dirname, 'public')))

const options = [
  'Transactions in Depth',
  'Worker Queues',
  'RPC over Reds (depends on "Queues")',
  'Lua Stored Procedures',
  'Distributed Locking (depends on "Lua Scripting")'
]

const initial = options.reduce((list, option) => list.concat([0, option]), [])
redis.zadd('votes', 'nx', initial)

app.get('/options', (req, res) => {
  res.json(options)
})

app.get('/votes', (req, res) => {
  redis.zrevrangebyscore('votes', '+inf', 0, 'withscores', (_err, votes) => {
    let result = {}
    for (let i = 0; i < votes.length; i += 2) {
      result[votes[i]] = Number(votes[i + 1])
    }
    res.json(result)
  })
})

pubsub.subscribe('vote')

pubsub.on('message', (channel, message) => {
  console.log('< Broadcasting vote for ' + message)
  io.emit('vote', message)
})

io.on('connection', (socket) => {
  const address = socket.request.connection.remoteAddress
  console.log('> Received ws connection ' + socket.id + ' from ' + address)
  socket.on('vote', (vote) => {
    console.log('> Received vote for ' + vote + ' from ' + address)
    redis.zincrby('votes', 1, vote)
    redis.publish('vote', vote)
  })
})

const PORT = process.env.PORT || 3000

http.listen(PORT, () => {
  console.log(`Listening on Port ${PORT}`)
})
