<template>
    <button @click="goToUrl()" class="button">
        <slot></slot>
    </button>
</template>

<script>
    export default {
        props: ['url'],

        methods: {
            goToUrl() {
                window.location.href = this.url;
            }
        }
    }
</script>