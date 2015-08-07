Vue.component('spark-team-settings-membership-screen', {
    /*
     * Bootstrap the component. Load the initial data.
     */
    ready: function () {
        this.getTeam();
    },


    /*
     * Initial state of the component's data.
     */
    data: function () {
        return {
            user: null,
            team: null,
            leavingTeam: false,

            sendInviteForm: {
                email: '',
                errors: [],
                sending: false,
                sent: false
            }
        };
    },


    computed: {
        /*
         * Determine if all necessary data has been loaded.
         */
        everythingIsLoaded: function () {
            return this.user && this.team;
        },


        /*
         * Get all users except for the current user.
         */
        teamUsersExceptMe: function () {
            self = this;

            return _.reject(this.team.users, function (user) {
                return user.id === self.user.id;
            });
        }
    },


    events: {
        /*
         * Handle the "userRetrieved" event.
         */
        userRetrieved: function (user) {
            this.user = user;
        }
    },


    methods: {
        /*
         * Get the current team from the API.
         */
        getTeam: function () {
            this.$http.get('/spark/api/teams/' + TEAM_ID)
                .success(function (team) {
                    this.team = team;
                });
        },


        /*
         * Send an invitation to a new user.
         */
        sendInvite: function (e) {
            e.preventDefault();

            this.sendInviteForm.errors = [];
            this.sendInviteForm.sent = false;
            this.sendInviteForm.sending = true;

            this.$http.post('/settings/teams/' + TEAM_ID + '/invitations', this.sendInviteForm)
                .success(function (team) {
                    this.team = team;

                    this.sendInviteForm.email = '';
                    this.sendInviteForm.sent = true;
                    this.sendInviteForm.sending = false;
                })
                .error(function (errors) {
                    this.sendInviteForm.sending = false;
                    setErrorsOnForm(this.sendInviteForm, errors);
                });
        },


        /*
         * Cancel an existing invitation.
         */
        cancelInvite: function (invite) {
            this.team.invitations = _.reject(this.team.invitations, function (i) {
                return i.id === invite.id;
            });

            this.$http.delete('/settings/teams/' + TEAM_ID + '/invitations/' + invite.id);
        },


        /*
         * Edit an existing team member.
         */
        editTeamMember: function (teamUser) {
            //
        },


        /*
         * Remove an existing team member from the team.
         */
        removeTeamMember: function (teamUser) {
            this.team.users = _.reject(this.team.users, function (u) {
                return u.id == teamUser.id;
            });

            this.$http.delete('/settings/teams/' + TEAM_ID + '/members/' + teamUser.id);
        },


        /*
         * Leave the team.
         */
        leaveTeam: function () {
            this.leavingTeam = true;

            this.$http.delete('/settings/teams/' + TEAM_ID + '/membership')
                .success(function () {
                    window.location = '/settings?tab=teams';
                });
        },


        /*
         * Determine if the current user owns the given team.
         */
        userOwns: function (team) {
            if ( ! this.user) {
                return false;
            }

            return this.user.id === team.owner_id;
        }
    }
});