<!DOCTYPE html>
<html>
    <head>
        <title>PA195 Redis Demo</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1>PA195 NoSQL Databases </h1>
            </div>
            <p>
                <strong>Redis PubSub Demo</strong>. Results are displayed in real time using PubSub and Socket.io.
                Note that results will be lost upon refresh.
            </p>
            <div class="row">
                <div class="col-sm-12 col-md-4">
                    <h2>Vote</h2>

                    <div v-show="voteCount >= 5" class="alert alert-danger" role="alert">You already voted 5 times!</div>

                    <select class="form-control" v-model="category">
                        <option v-for="name in categories">@{{ name }}</option>
                    </select>

                    <button @click="vote()"
                            :class="voteCount >= 5 ? 'disabled' : ''"
                            type="button"
                            class="btn btn-block btn-primary"
                            style="margin-top: 10px;"
                    >
                        Vote!
                    </button>
                </div>

                <div class="col-sm-12 col-md-8">
                    <h2>Results</h2>

                    <span v-if="!results.length">No results yet.</span>

                    <table v-if="results.length" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>
                                    Topic
                                </th>
                                <th>
                                    Votes
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="result in results">
                                <td>
                                    @{{ result.name }}
                                </td>
                                <td>
                                    @{{ result.count }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <hr>

                    <input type="checkbox" v-model="showStream"> Show stream
                    <br>

                    <div v-show="showStream" class="container">
                        <div v-for="s in stream" class="row" style="font-family: monospace;">
                            [@{{ s.time }}] Someone voted for "@{{ s.category }}"
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </body>

    <script src="https://cdn.socket.io/socket.io-1.3.5.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/vue/1.0.21/vue.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/0.7.0/vue-resource.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.min.js"></script>

    <script>
    Vue.use(VueResource);

    new Vue({
        el: 'body',

        ready: function() {
            var self = this;

            // Connect to emiting server
            var socket = io('http://192.168.10.10:3000');
            socket.on("votes:App\\Events\\Vote", function(message) {
                var name = message.data.vote;

                self.stream.push({
                    time: moment().format('HH:mm:ss'),
                    category: name
                });

                self.votes.push(name);
            });

            // Clear the stream every 15s
            setInterval(function() {
                self.stream = [];
            }, 15000);

            // Load vote count
            this.voteCount = parseInt(localStorage.getItem('voteCount')) || 0;
        },

        data: {
            votes: [],
            stream: [],
            showStream: false,
            voteCount: 0,
            category: 'Everything',
            categories: [
                'Everything', 'Test 1', 'Test 2', 'Etc.'
            ]
        },

        computed: {
            results: function() {
                return _.chain(this.votes)
                    .countBy(function(v) {
                        return v;
                    })
                    .map(function(v, i) {
                        return { name: i, count: v };
                    })
                    .sortBy(function(v) {
                        return -v.count;
                    })
                    .value();
            }
        },

        methods: {
            vote: function() {
                if (this.voteCount >= 5) {
                    return;
                }

                this.voteCount = this.voteCount + 1;
                localStorage.setItem('voteCount', this.voteCount);

                this.$http.post('/vote', {
                    vote: this.category
                }).then(function(response) {
                    console.log(response.data);
                }).catch(function(response) {
                    console.log(response.data);
                });
            }
        }
    })
    </script>

    <script>
        // var socket = io('http://localhost:3000');

    </script>
</html>
