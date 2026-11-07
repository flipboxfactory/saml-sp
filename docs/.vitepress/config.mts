import { defineConfig } from 'vitepress'

export default defineConfig({
    title: 'SAML SSO Service Provider',
    description: 'SAML SSO Service Provider plugin for Craft CMS',
    base: '/',
    head: [['link', { rel: 'icon', href: '/icon.svg' }]],
    themeConfig: {
        logo: '/icon.svg',
        search: { provider: 'local' },
        editLink: {
            pattern: 'https://github.com/flipboxfactory/saml-sp/edit/master/docs/:path',
        },
        socialLinks: [
            { icon: 'github', link: 'https://github.com/flipboxfactory/saml-sp' },
        ],
        nav: [
            { text: 'Details', link: 'https://www.flipboxdigital.com/craft-cms-plugins/saml-service-provider' },
            { text: 'Changelog', link: 'https://github.com/flipboxfactory/saml-sp/blob/master/CHANGELOG.md' },
        ],
        outline: [2, 4],
        sidebar: [
            {
                text: 'Getting Started',
                items: [
                    { text: 'Introduction', link: '/' },
                    { text: 'Installation / Upgrading', link: '/installation' },
                    { text: 'Quick Start', link: '/quick-start' },
                    { text: 'Support', link: '/support' },
                    { text: 'FAQs', link: '/faqs' },
                ],
            },
            {
                text: 'Configure',
                items: [
                    { text: 'Overview', link: '/configure/' },
                    { text: 'Login', link: '/configure/login' },
                    { text: 'Logout', link: '/configure/logout' },
                    { text: 'User Groups / Permissions', link: '/configure/groups' },
                    { text: 'KeyChain', link: '/configure/keychain' },
                    { text: 'Settings', link: '/configure/settings' },
                    { text: 'Events', link: '/configure/events' },
                ],
            },
            {
                text: 'IdPs',
                items: [
                    { text: 'IdPs', link: '/idps/' },
                    { text: 'Azure AD', link: '/idps/azure-ad' },
                    { text: 'Okta', link: '/idps/okta' },
                ],
            },
            {
                text: 'Examples',
                items: [
                    { text: 'Examples', link: '/examples/' },
                    { text: 'Multi-Site with CP Login', link: '/examples/multi-site-with-cp-login' },
                ],
            },
        ],
    },
})
