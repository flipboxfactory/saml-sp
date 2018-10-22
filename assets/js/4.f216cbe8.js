(window.webpackJsonp=window.webpackJsonp||[]).push([[4],{165:function(s,t,a){"use strict";a.r(t);var n=a(0),e=Object(n.a)({},function(){this.$createElement;this._self._c;return this._m(0)},[function(){var s=this,t=s.$createElement,a=s._self._c||t;return a("div",{staticClass:"content"},[a("h1",[s._v("Events")]),a("p",[s._v("There are events within the plugin that developers can hook into.")]),a("h2",{attrs:{id:"list"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#list","aria-hidden":"true"}},[s._v("#")]),s._v(" List")]),a("div",{pre:!0},[a("ul",[a("li",[a("code",[s._v("\\flipbox\\saml\\sp\\services\\messages\\AuthnRequest::EVENT_AFTER_MESSAGE_CREATED")]),a("ul",[a("li",[s._v("Use to modify AuthNRequest Message")])])]),a("li",[a("code",[s._v("\\flipbox\\saml\\sp\\services\\Login::EVENT_BEFORE_RESPONSE_TO_USER")]),a("ul",[a("li",[s._v("Use to modify user or response before user is synced with Saml response attributes and saved")])])]),a("li",[a("code",[s._v("\\flipbox\\saml\\sp\\services\\Login::EVENT_AFTER_RESPONSE_TO_USER")]),a("ul",[a("li",[s._v("User to modify user after the user has been synced with Saml Response attributes and saved.")])])])])]),a("h2",{attrs:{id:"examples"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#examples","aria-hidden":"true"}},[s._v("#")]),s._v(" Examples")]),a("h3",{attrs:{id:"assign-user-to-a-user-group-based-on-a-property"}},[a("a",{staticClass:"header-anchor",attrs:{href:"#assign-user-to-a-user-group-based-on-a-property","aria-hidden":"true"}},[s._v("#")]),s._v(" Assign User to a User Group Based on a Property")]),a("div",{staticClass:"language-php extra-class"},[a("pre",{pre:!0,attrs:{class:"language-php"}},[a("code",[s._v("Event"),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token function"}},[s._v("on")]),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),s._v("\n            \\"),a("span",{attrs:{class:"token package"}},[s._v("flipbox"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("saml"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("sp"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("services"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("Login")]),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token keyword"}},[s._v("class")]),a("span",{attrs:{class:"token punctuation"}},[s._v(",")]),s._v("\n            \\"),a("span",{attrs:{class:"token package"}},[s._v("flipbox"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("saml"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("sp"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("services"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("Login")]),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token constant"}},[s._v("EVENT_AFTER_RESPONSE_TO_USER")]),a("span",{attrs:{class:"token punctuation"}},[s._v(",")]),s._v("\n            "),a("span",{attrs:{class:"token keyword"}},[s._v("function")]),s._v(" "),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),s._v("\\"),a("span",{attrs:{class:"token package"}},[s._v("flipbox"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("saml"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("sp"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("events"),a("span",{attrs:{class:"token punctuation"}},[s._v("\\")]),s._v("UserLogin")]),s._v(" "),a("span",{attrs:{class:"token variable"}},[s._v("$event")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),s._v(" "),a("span",{attrs:{class:"token punctuation"}},[s._v("{")]),s._v("\n\n                "),a("span",{attrs:{class:"token comment"}},[s._v("/** @var \\craft\\elements\\User $user */")]),s._v("\n                "),a("span",{attrs:{class:"token variable"}},[s._v("$user")]),s._v(" "),a("span",{attrs:{class:"token operator"}},[s._v("=")]),s._v(" "),a("span",{attrs:{class:"token variable"}},[s._v("$event")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token property"}},[s._v("user")]),a("span",{attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n\n                "),a("span",{attrs:{class:"token comment"}},[s._v("/**\n                 * get existing groups\n                 */")]),s._v("\n                "),a("span",{attrs:{class:"token variable"}},[s._v("$groups")]),s._v(" "),a("span",{attrs:{class:"token operator"}},[s._v("=")]),s._v(" "),a("span",{attrs:{class:"token punctuation"}},[s._v("[")]),a("span",{attrs:{class:"token punctuation"}},[s._v("]")]),a("span",{attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n                "),a("span",{attrs:{class:"token keyword"}},[s._v("foreach")]),s._v(" "),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{attrs:{class:"token variable"}},[s._v("$user")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token function"}},[s._v("getGroups")]),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),s._v(" "),a("span",{attrs:{class:"token keyword"}},[s._v("as")]),s._v(" "),a("span",{attrs:{class:"token variable"}},[s._v("$group")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),s._v(" "),a("span",{attrs:{class:"token punctuation"}},[s._v("{")]),s._v("\n                    "),a("span",{attrs:{class:"token variable"}},[s._v("$groups")]),a("span",{attrs:{class:"token punctuation"}},[s._v("[")]),a("span",{attrs:{class:"token variable"}},[s._v("$group")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token property"}},[s._v("id")]),a("span",{attrs:{class:"token punctuation"}},[s._v("]")]),s._v(" "),a("span",{attrs:{class:"token operator"}},[s._v("=")]),s._v(" "),a("span",{attrs:{class:"token variable"}},[s._v("$group")]),a("span",{attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n                "),a("span",{attrs:{class:"token punctuation"}},[s._v("}")]),s._v("\n                \n                "),a("span",{attrs:{class:"token comment"}},[s._v("/**\n                 * Logic to Determine Is Admin\n                 * (return if they aren't admin)\n                 */")]),s._v("\n                "),a("span",{attrs:{class:"token keyword"}},[s._v("if")]),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{attrs:{class:"token operator"}},[s._v("!")]),s._v(" MyUserHelper"),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token function"}},[s._v("isAdminUser")]),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{attrs:{class:"token variable"}},[s._v("$user")]),a("span",{attrs:{class:"token punctuation"}},[s._v(",")]),s._v(" "),a("span",{attrs:{class:"token variable"}},[s._v("$response")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{attrs:{class:"token punctuation"}},[s._v("{")]),s._v("\n                    "),a("span",{attrs:{class:"token keyword"}},[s._v("return")]),a("span",{attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n                "),a("span",{attrs:{class:"token punctuation"}},[s._v("}")]),s._v("\n\n                "),a("span",{attrs:{class:"token comment"}},[s._v("/**\n                 * get the default group by handle\n                 */")]),s._v("\n                "),a("span",{attrs:{class:"token variable"}},[s._v("$group")]),s._v(" "),a("span",{attrs:{class:"token operator"}},[s._v("=")]),s._v(" \\"),a("span",{attrs:{class:"token package"}},[s._v("Craft")]),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token variable"}},[s._v("$app")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token function"}},[s._v("getUserGroups")]),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token function"}},[s._v("getGroupByHandle")]),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{attrs:{class:"token single-quoted-string string"}},[s._v("'myAdminGroup'")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n\n                "),a("span",{attrs:{class:"token comment"}},[s._v("/**\n                 * add it to the group array\n                 */")]),s._v("\n                "),a("span",{attrs:{class:"token variable"}},[s._v("$groups")]),a("span",{attrs:{class:"token punctuation"}},[s._v("[")]),a("span",{attrs:{class:"token variable"}},[s._v("$group")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token property"}},[s._v("id")]),a("span",{attrs:{class:"token punctuation"}},[s._v("]")]),s._v(" "),a("span",{attrs:{class:"token operator"}},[s._v("=")]),s._v(" "),a("span",{attrs:{class:"token variable"}},[s._v("$group")]),a("span",{attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n\n                "),a("span",{attrs:{class:"token comment"}},[s._v("/**\n                 * get an array of ids\n                 */")]),s._v("\n                "),a("span",{attrs:{class:"token variable"}},[s._v("$groupIds")]),s._v(" "),a("span",{attrs:{class:"token operator"}},[s._v("=")]),s._v(" "),a("span",{attrs:{class:"token function"}},[s._v("array_map")]),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),s._v("\n                    "),a("span",{attrs:{class:"token keyword"}},[s._v("function")]),s._v(" "),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{attrs:{class:"token variable"}},[s._v("$group")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),s._v(" "),a("span",{attrs:{class:"token punctuation"}},[s._v("{")]),s._v("\n                        "),a("span",{attrs:{class:"token keyword"}},[s._v("return")]),s._v(" "),a("span",{attrs:{class:"token variable"}},[s._v("$group")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token property"}},[s._v("id")]),a("span",{attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n                    "),a("span",{attrs:{class:"token punctuation"}},[s._v("}")]),a("span",{attrs:{class:"token punctuation"}},[s._v(",")]),s._v("\n                    "),a("span",{attrs:{class:"token variable"}},[s._v("$groups")]),s._v("\n                "),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n\n                "),a("span",{attrs:{class:"token comment"}},[s._v("/**\n                 * Assign them to the user\n                 */")]),s._v("\n                "),a("span",{attrs:{class:"token keyword"}},[s._v("if")]),s._v(" "),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),s._v("\\"),a("span",{attrs:{class:"token package"}},[s._v("Craft")]),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token punctuation"}},[s._v(":")]),a("span",{attrs:{class:"token variable"}},[s._v("$app")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token function"}},[s._v("getUsers")]),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token function"}},[s._v("assignUserToGroups")]),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{attrs:{class:"token variable"}},[s._v("$user")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token property"}},[s._v("id")]),a("span",{attrs:{class:"token punctuation"}},[s._v(",")]),s._v(" "),a("span",{attrs:{class:"token variable"}},[s._v("$groupIds")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),s._v(" "),a("span",{attrs:{class:"token punctuation"}},[s._v("{")]),s._v("\n                    "),a("span",{attrs:{class:"token comment"}},[s._v("/**\n                     * Set the groups back on the user just in case it's being used after this.\n                     *\n                     * This may seem strange because the they do this in the `assignUserToGroups`\n                     * method but the user they set the groups to isn't *this* user object,\n                     * so this is needed.\n                     */")]),s._v("\n                    "),a("span",{attrs:{class:"token variable"}},[s._v("$user")]),a("span",{attrs:{class:"token operator"}},[s._v("-")]),a("span",{attrs:{class:"token operator"}},[s._v(">")]),a("span",{attrs:{class:"token function"}},[s._v("setGroups")]),a("span",{attrs:{class:"token punctuation"}},[s._v("(")]),a("span",{attrs:{class:"token variable"}},[s._v("$groups")]),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n                "),a("span",{attrs:{class:"token punctuation"}},[s._v("}")]),s._v("\n            "),a("span",{attrs:{class:"token punctuation"}},[s._v("}")]),s._v("\n        "),a("span",{attrs:{class:"token punctuation"}},[s._v(")")]),a("span",{attrs:{class:"token punctuation"}},[s._v(";")]),s._v("\n")])])])])}],!1,null,null,null);e.options.__file="events.md";t.default=e.exports}}]);