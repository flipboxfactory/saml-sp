module.exports = {
    title: 'SAML Service Provider (SSO)',
    description: 'Single sign-on and single logout for Craft CMS',
    base: '/',
    //theme: 'flipbox',
    themeConfig: {
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
            {text: 'Documentation', link: 'https://saml-sp.flipboxfactory.com'},
            {text: 'Changelog', link: 'https://github.com/flipboxfactory/saml-sp/blob/master/CHANGELOG.md'},
            {text: 'Repo', link: 'https://github.com/flipboxfactory/saml-sp'}
        ],
        sidebar: {
            '/': [
                {
                    title: 'Getting Started',
                    collapsable: true,
                    children: [
                        ['/', 'Introduction'],
                        ['/requirements', 'Requirements'],
                        ['/installation', 'Installation / Upgrading'],
                        ['/support', 'Support'],
                    ]
                },
                {
                    title: 'Configure',
                    collapsable: true,
                    children: [
                        ['/configure/', 'Overview'],
                        ['/configure/login', 'Login'],
                        ['/configure/logout', 'Logout'],
                    ]
                },
                {
                    title: 'Provider Examples',
                    collapsable: true,
                    children: [
                        ['/providers/', 'Overview']
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