var mkws_config = { sp_auth_credentials: "emusik_no462/emusik_no462" };


mkws.registerWidgetType('indexdata-artist-block', function () {
	if (!this.config.perpage) this.config.perpage = 5;
	if (!this.config.sort) this.config.sort = "position";
	this.team.registerTemplate('wimp2', '\
<ul>\
{{#each hits}}\
  <li>\
    <span class="left">\
      {{#mkws-first md-electronic-url}}\
        <a href="{{this}}">\
      {{/mkws-first}}\
      <div>{{md-title}}</div></a>\
      <div>{{#if md-date}}{{md-date}}{{/if}}</div>\
    </span>\
    <span class="right">\
      {{#if md-thumburl}}\
        <img class="wimp-img" src="{{md-thumburl}}" />\
      {{/if}}\
    </span>\
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

