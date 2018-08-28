module.exports = {
    title: 'SAML SSO Service Provider',
    description: 'SAML SSO Service Provider for CraftCMS',
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
                    ]
                },
                {
                    title: 'Templating',
                    collapsable: true,
                    children: [
                        ['/templating/', 'Overview']
                    ]
                },
                // {
                //     title: 'Services',
                //     collapsable: true,
                //     children: [
                //         ['/services/elements', 'Organization Elements'],
                //         ['/services/organization-types', 'Organization Types'],
                //         ['/services/users', 'User Elements'],
                //         ['/services/user-types', 'User Types']
                //     ]
                // },
                // {
                //     title: 'Objects',
                //     collapsable: true,
                //     children: [
                //         ['/objects/organization', 'Organization'],
                //         ['/objects/organization-type', 'Organization Type'],
                //         ['/objects/organization-type-site-settings', 'Organization Type Site Settings'],
                //         ['/objects/settings', 'Settings'],
                //         ['/objects/user-type', 'User Type'],
                //         ['/objects/user', 'User']
                //     ]
                // },
                // {
                //     title: 'Queries',
                //     collapsable: true,
                //     children: [
                //         ['/queries/organization', 'Organization Query'],
                //         ['/queries/organization-type', 'Organization Type Query'],
                //         ['/queries/user', 'User Query'],
                //         ['/queries/user-type', 'User Type Query']
                //     ]
                // }
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