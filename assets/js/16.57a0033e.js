(window.webpackJsonp=window.webpackJsonp||[]).push([[16],{378:function(e,t,s){"use strict";s.r(t);var a=s(45),r=Object(a.a)({},(function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("ContentSlotsDistributor",{attrs:{"slot-key":e.$parent.slotKey}},[s("h2",{attrs:{id:"what-is-saml"}},[s("a",{staticClass:"header-anchor",attrs:{href:"#what-is-saml"}},[e._v("#")]),e._v(" What is SAML?")]),e._v(" "),s("p",[e._v("Answer: "),s("a",{attrs:{href:"https://en.wikipedia.org/wiki/Security_Assertion_Markup_Language",target:"_blank",rel:"noopener noreferrer"}},[e._v("Here's wikipedia's answer"),s("OutboundLink")],1)]),e._v(" "),s("blockquote",[s("p",[e._v("Security Assertion Markup Language (SAML, pronounced SAM-el) is an open standard for exchanging authentication and authorization data between parties, in particular, between an identity provider and a service provider. SAML is an XML-based markup language for security assertions (statements that service providers use to make access-control decisions). SAML is also:")]),e._v(" "),s("ul",[s("li",[e._v("A set of XML-based protocol messages")]),e._v(" "),s("li",[e._v("set of protocol message bindings")]),e._v(" "),s("li",[e._v("set of profiles (utilizing all of the above)")]),e._v(" "),s("li",[e._v("The single most important use case that SAML addresses is web browser single sign-on (SSO). Single sign-on is relatively easy to accomplish within a security domain (using cookies, for example) but extending SSO across security domains is more difficult and resulted in the proliferation of non-interoperable proprietary technologies. The SAML Web Browser SSO profile was specified and standardized to promote interoperability.")])])]),e._v(" "),s("div",{staticClass:"custom-block tip"},[s("p",{staticClass:"custom-block-title"},[e._v("TIP")]),e._v(" "),s("h3",{attrs:{id:"saml-sp-101"}},[s("a",{staticClass:"header-anchor",attrs:{href:"#saml-sp-101"}},[e._v("#")]),e._v(" SAML SP 101")]),e._v(" "),s("p",[e._v("This plugin is concerned with the Service Provider (SP) site. It's role is to receive authentication and authorization\nmessages from the Identity Provider (IdP). Upon a successful IdP login, a message login status (success or not),\nsession info, and user attributes are POSTed (usually POST, sometimes GET) to the Craft site and received and validated by the plugin.\nThen the user is synced, logged in, and redirected to where the user initially intended to go.")])]),e._v(" "),s("h2",{attrs:{id:"what-is-the-entity-id"}},[s("a",{staticClass:"header-anchor",attrs:{href:"#what-is-the-entity-id"}},[e._v("#")]),e._v(" What is the Entity ID?")]),e._v(" "),s("p",[e._v("An entity ID is a globally unique name for a SAML entity, either an Identity Provider (IdP) or a Service Provider (SP).\nRecent plugin updates has made it so Entity ID is editable but be careful when doing so -- the other providers may still\nhave that ID as their link to yours.")]),e._v(" "),s("h2",{attrs:{id:"is-there-multi-site-support"}},[s("a",{staticClass:"header-anchor",attrs:{href:"#is-there-multi-site-support"}},[e._v("#")]),e._v(" Is there "),s("a",{attrs:{href:"https://docs.craftcms.com/v3/sites.html",target:"_blank",rel:"noopener noreferrer"}},[e._v("Multi-Site"),s("OutboundLink")],1),e._v(" support?")]),e._v(" "),s("p",[e._v("Answer: Yes, as of 2.0.1, a multi-site configuration should work seamlessly.")]),e._v(" "),s("div",{staticClass:"custom-block tip"},[s("p",{staticClass:"custom-block-title"},[e._v("TIP")]),e._v(" "),s("p",[e._v('Multi-Site\nWhen you create "My Provider", the Entity ID and endpoints default to the current site.\nThis should not create any problems. If the appearance is a aesthetic issue for whatever reason,\nuse the Primary site when creating "My Provider". You can also override the default Entity Id if desired.')])]),e._v(" "),s("h2",{attrs:{id:"does-the-plugin-support-a-metadata-url"}},[s("a",{staticClass:"header-anchor",attrs:{href:"#does-the-plugin-support-a-metadata-url"}},[e._v("#")]),e._v(" Does the plugin support a metadata URL?")]),e._v(" "),s("p",[e._v("Answer: Yes, as of version 2.1. If you use providers like ADFS who periodically renew their metadata and certificates,\nyou can automate the syncing by running the following command:")]),e._v(" "),s("div",{staticClass:"language-shell script extra-class"},[s("pre",{pre:!0,attrs:{class:"language-shell"}},[s("code",[e._v("php craft saml-sp/metadata/refresh-with-url "),s("span",{pre:!0,attrs:{class:"token operator"}},[e._v("<")]),e._v("uid"),s("span",{pre:!0,attrs:{class:"token operator"}},[e._v(">")]),e._v("\n")])])]),s("p",[e._v("An example cronjob would look like this:")]),e._v(" "),s("div",{staticClass:"language-shell script extra-class"},[s("pre",{pre:!0,attrs:{class:"language-shell"}},[s("code",[s("span",{pre:!0,attrs:{class:"token number"}},[e._v("0")]),e._v(" "),s("span",{pre:!0,attrs:{class:"token number"}},[e._v("0")]),e._v(" * * * php craft saml-sp/metadata/refresh-with-url "),s("span",{pre:!0,attrs:{class:"token operator"}},[e._v("<")]),e._v("uid"),s("span",{pre:!0,attrs:{class:"token operator"}},[e._v(">")]),e._v(" "),s("span",{pre:!0,attrs:{class:"token operator"}},[e._v("||")]),e._v(" /opt/notify-admins-on-fail.sh\n")])])]),s("h2",{attrs:{id:"error-trying-to-get-property-keychain-of-non-object"}},[s("a",{staticClass:"header-anchor",attrs:{href:"#error-trying-to-get-property-keychain-of-non-object"}},[e._v("#")]),e._v(" Error: Trying to get property 'keychain' of non-object")]),e._v(" "),s("p",[e._v("Answer: This usually means \"My Provider\", the sites metadata/provider can't be found, or possibly, hasn't\nbeen created. Go to "),s("code",[e._v("https://<your domain>/admin/saml-sp/metadata/my-provider")]),e._v(" and create a new provider\nfor your site.")])])}),[],!1,null,null,null);t.default=r.exports}}]);