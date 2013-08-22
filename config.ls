exports.config =
  # See http://brunch.io/#documentation for docs.
  modules:
    wrapper: (path, data) ->
        """
(function() {
  #{data}
}).call(this);\n\n
        """
  paths:
    public: "_public"
  files:
    javascripts:
      joinTo:
        "js/app.js": /^app/
        "js/vendor.js": /^vendor/
    stylesheets:
      joinTo:
        "css/app.css": /^(app|vendor)/
    templates:
      joinTo:
        "js/dontUseMe": /^app/
  plugins:
    jade:
      options:
        pretty: yes
    static_jade:
      extension: ".static.jade"
      path: [/^app/]
      asset: "app/webdata"
