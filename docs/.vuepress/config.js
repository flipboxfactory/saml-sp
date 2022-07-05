module.exports = {
    title: 'SAML SSO Service Provider',
    description: 'SAML SSO Service Provider plugin for Craft CMS',
    base: '/',
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
                    children: [
                        ['/', 'Introduction'],
                        ['/installation', 'Installation / Upgrading'],
                        ['/quick-start', 'Quick Start'],
                        ['/support', 'Support'],
                        ['/faqs', 'FAQs'],
                    ]
                },
                {
                    title: 'Configure',
                    children: [
                        ['/configure/', 'Overview'],
                        ['/configure/login', 'Login'],
                        ['/configure/logout', 'Logout'],
                        ['/configure/groups', 'User Groups / Permissions'],
                        ['/configure/keychain', 'KeyChain'],
                        ['/configure/settings', 'Settings'],
                        ['/configure/events', 'Events'],
                    ]
                },
                {
                    title: 'IdPs',
                    children: [
                        ['/idps/', 'IdPs'],
                        ['/idps/azure-ad', 'Azure AD'],
                        ['/idps/okta', 'Okta'],
                    ]
                },
                {
                    title: 'Examples',
                    children: [
                        ['/examples/', 'Examples'],
                        ['/examples/multi-site-with-cp-login', 'Multi-Site with CP Login'],
                    ]
                },
            ]
        }
    },
    markdown: {
        anchor: { level: [2, 3, 4] },
        toc: { includeLevel: [3] },
        config(md) {
            md.use(require('vuepress-theme-flipbox/markup'))
        }
    }
}
