module.exports = {
    title: 'SAML SSO Service Provider',
    description: 'Single sign-on and single logout for Craft CMS',
    base: '/',
    //theme: 'flipbox',
    themeConfig: {
        logo: '/icon.svg',
        docsRepo: 'flipboxfactory/saml-sp',
        docsDir: 'docs',
        docsBranch: 'master',
        editLinks: true,
        search: true,
        searchMaxSuggestions: 10,
        codeLanguages: {
            twig: 'Twig',
            php: 'PHP',
            json: 'JSON',
            // any other languages you want to include in code toggles...
        },
        nav: [
            {text: 'Details', link: 'https://www.flipboxdigital.com/craft-cms-plugins/saml-service-provider'},
            {text: 'Changelog', link: 'https://github.com/flipboxfactory/saml-sp/blob/master/CHANGELOG.md'},
            {text: 'Repo', link: 'https://github.com/flipboxfactory/saml-sp'}
        ],
        sidebar: {
            '/': [
                {
                    title: 'Getting Started',
                    collapsable: false,
                    children: [
                        ['/', 'Introduction'],
                        ['/installation', 'Installation / Upgrading'],
                        ['/support', 'Support'],
                    ]
                },
                {
                    title: 'Configure',
                    collapsable: false,
                    children: [
                        ['/configure/', 'Overview'],
                        ['/configure/login', 'Login'],
                        ['/configure/logout', 'Logout'],
                        ['/configure/keychain', 'KeyChain'],
                        ['/configure/settings', 'Settings'],
                        ['/configure/events', 'Events'],
                    ]
                },
            ]
        }
    },
    markdown: {
        anchor: { level: [2, 3] },
        toc: { includeLevel: [3] },
        config(md) {
            let markup = require('./markup') // TODO Change after using theme
            md.use(markup)
        }
    }
}