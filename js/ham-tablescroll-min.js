// jQuery table scroll, http://www.farinspace.com

(function(a){function l(){if(g)return g;var b=a('<div style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"><div style="height:100px;"></div></div>');a("body").append(b);var c=a("div",b).innerWidth();b.css("overflow-y","auto");var n=a("div",b).innerWidth();a(b).remove();return g=c-n}var g=0;a.fn.tableScroll=function(b){if(b=="undo")b=a(this).parent().parent(),b.hasClass("tablescroll_wrapper")&&(b.find(".tablescroll_head thead").prependTo(this),b.find(".tablescroll_foot tfoot").appendTo(this), b.before(this),b.empty());else{var c=a.extend({},a.fn.tableScroll.defaults,b);if(a(this).height()<=c.height)return this;c.scrollbarWidth=l();this.each(function(){var b=c.flush,d=a(this).addClass("tablescroll_body"),e=a('<div class="tablescroll_wrapper"></div>').insertBefore(d).append(d);e.parent("div").hasClass(c.containerClass)||a("<div></div>").addClass(c.containerClass).insertBefore(e).append(e);var f=c.width?c.width:d.outerWidth();e.css({width:f+"px",height:c.height+"px",overflow:"auto"});d.css("width", f+"px");var i=e.outerWidth()-f;e.css({width:f-i+c.scrollbarWidth+"px"});d.css("width",f-i+"px");d.outerHeight()<=c.height&&(e.css({height:"auto",width:f-i+"px"}),b=!1);var i=a("thead",d).length?!0:!1,g=a("tfoot",d).length?!0:!1,m=a("thead tr:first",d),l=a("tbody tr:first",d),o=a("tfoot tr:first",d),h=0;a("th, td",m).each(function(b){h=a(this).width();a("th:eq("+b+"), td:eq("+b+")",m).css("width",h+"px");a("th:eq("+b+"), td:eq("+b+")",l).css("width",h+"px");g&&a("th:eq("+b+"), td:eq("+b+")",o).css("width", h+"px")});if(i)var j=a('<table class="'+c.header_class+'" cellspacing="0"></table>').insertBefore(e).prepend(a("thead",d));if(g)var k=a('<table class="'+c.footer_class+'" cellspacing="0"></table>').insertAfter(e).prepend(a("tfoot",d));j!=void 0&&(j.css("width",f+"px"),b&&(a("tr:first th:last, tr:first td:last",j).css("width",h+c.scrollbarWidth+"px"),j.css("width",e.outerWidth()+"px")));k!=void 0&&(k.css("width",f+"px"),b&&(a("tr:first th:last, tr:first td:last",k).css("width",h+c.scrollbarWidth+"px"), k.css("width",e.outerWidth()+"px")))});return this}};a.fn.tableScroll.defaults={flush:!0,width:null,height:100,containerClass:"tablescroll",header_class:"tablescroll_head",footer_class:"tablescroll_foot"}})(jQuery);