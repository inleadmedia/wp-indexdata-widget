var mkws_config = { sp_auth_credentials: "emusik_no462/emusik_no462" };


mkws.registerWidgetType('indexdata-artist-block', function () {
	if (!this.config.perpage) this.config.perpage = 5;
	if (!this.config.sort) this.config.sort = "position";
	this.team.registerTemplate('wimp2', '\
<ul>\
{{#each hits}}\
  <li>\
    {{#mkws-first md-electronic-url}}\
    <a href="{{this}}">\
    {{/mkws-first}}\
      {{md-title}}\
    </a>\
<img src="{{md-thumburl}}" />\
  {{#if md-title-remainder}}\
    <span>{{md-title-remainder}}</span>\
  {{/if}}\
  {{#if md-title-responsibility}}\
    <span><i>{{md-title-responsibility}}</i></span>\
  {{/if}}\
  </li>\
{{/each}}\
</ul>\
');
	var that = this;
	var template = that.team.loadTemplate(that.config.template || "wimp2");
	this.team.queue("records").subscribe(function (data) {
		that.node.html(template(data));
	});
	that.autosearch();
});

