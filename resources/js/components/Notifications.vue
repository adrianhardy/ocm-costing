<template>

    <b-nav-item-dropdown @shown="open()" right>
        <b-dropdown-header>Recent Notifications</b-dropdown-header>
        <template #button-content>
            <b-icon-bell />
            <b-badge v-show="liveCount > 0">{{ liveCount }}</b-badge>
        </template>
        <b-dropdown-item v-for="notification in notifications" :key="notification.id">
            <div class="small">
                <h6>{{ notification.data.title }}</h6>
                <p>{{ notification.data.message }}</p>
                <div>
                    <span class="text-muted">
                        {{ new Date(notification.created_at) | relativeDate }}
                    </span>
                    <a @click.prevent="clear(notification.id)" href="#" class="text-danger float-right">clear</a>
                </div>
            </div>
        </b-dropdown-item>
        <b-dropdown-item-button size="sm" variant="primary" v-if="more" @click.native.capture.stop="next()">
            Load More Notifications
        </b-dropdown-item-button>
        <b-dropdown-item-button size="sm" variant="danger" v-if="more" @click="clear('all')">
            Clear All Notifications
        </b-dropdown-item-button>

    </b-nav-item-dropdown>
 </template>

<script>
const audio = new Audio('/assets/mp3/notification.mp3');
import formatDistanceToNow from 'date-fns/formatDistanceToNow'
export default {
    filters: {
        relativeDate(date) {
            return formatDistanceToNow(date, {
                addSuffix: true
            });
        }
    },
    name: "Notifications",
    props: ['user-id', 'count'],
    data() {
        return {
            clearAll: false,
            more: false,
            currentPage: 1,
            notifications: [],
            liveCount: 0,
            perPage: 5
        }
    },
    methods: {
        clear(id) {
            console.log("putting to delete...");
            this.$http
                .put(`/api/notifications/${id}`, {mark_as_read: true})
                .then(response => {
                    this.liveCount = response.data.unread_count;
                })
            this.fetch();
        },
        open() {
            this.currentPage = 1;
            this.fetch();
        },
        /**
         * The crazy mark up on the component (@click.native.capture.stop)
         * was taken from here: https://stackoverflow.com/a/61330233
         * @param $event
         */
        next($event) {
            console.log("nexting");
            this.currentPage++;
            this.fetch();
        },
        fetch() {
            this.$http.get('/api/notifications', {params: {
                    page: this.currentPage,
                    per_page: this.perPage
                }
            }).then(response => {
                this.notifications = response.data.items;
                this.more = (response.data.pages > response.data.current_page);
            })
        }
    },
    mounted() {
        this.liveCount = this.count;
        window.Echo.private("App.User." + this.userId).notification(e => {
            audio.play();
            this.$bvToast.toast(e.message, {
                variant: e.variant,
                title: e.title
            });
            this.liveCount++;
        });
    },
    beforeDestroy() {
        window.Echo.leave("App.User." + this.userId);
    }
}
</script>
<style scoped>
    .b-nav-dropdown >>> .dropdown-item {
        padding: 0.55rem 1rem;
    }
    .b-nav-dropdown >>> .dropdown-item p {
        margin-bottom: 0;
    }
</style>
