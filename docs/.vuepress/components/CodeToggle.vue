<template>
    <div class="code-toggle">
        <ul class="code-language-switcher">
            <li v-for="language in languages">
                <a :class="{ active: language === selectedLanguage }" @click="setLanguage(language)">{{ $site.themeConfig.codeLanguages[language] }}</a>
            </li>
        </ul>
        <div v-for="language in languages">
            <slot :name="language" v-if="language == selectedLanguage" />
        </div>
    </div>
</template>

<script>
    export default {
        props: ['languages'],

        data() {
            return {
                selectedLanguage: this.languages[0]
            }
        },

        methods: {
            setLanguage(language) {
                this.selectedLanguage = language
            }
        }
    }
</script>

<style src="prismjs/themes/prism-coy.css"></style>