<script setup>
// const isLoggedin = () => {
//     return (localStorage.getItem('token')) != null;
// }

let isLoggedin = localStorage.getItem('token') != null;


</script>
<template>
    <div>

        <!-- <div class="card">
            <div class="card-header">
                <ul class="ul-list" v-if="isLoggedin">
                    <li><router-link to="/dashboard" class="li-menu">Dashboard</router-link></li>
                </ul>

                <ul v-else="" class="ul-list">
                    <li><router-link to="/" class="li-menu">Login</router-link></li>
                    <li><router-link to="/register" class="li-menu">Register</router-link></li>
                </ul>
            </div>
        </div> -->

        <!-- <router-view></router-view> -->
    </div>
</template>

<style>
.ul-list li {
    list-style: none;
    display: inline;
    padding: 0 5px 0 5px;
    text-decoration: none;
}

.ul-list li .li-menu {
    list-style: none;
    display: inline;
    padding: 0 5px 0 5px;
    text-decoration: none;
}
</style>