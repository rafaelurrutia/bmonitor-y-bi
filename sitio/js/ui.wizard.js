/**
 * A wizard widget that actually works with minimal configuration. (per jQuery's design philosophy)
 *
 * @name	jWizard jQuery UI Widget
 * @author	Dominic Barnes
 *
 * @requires jQuery
 * @requires jQuery UI (Widget Factory; ProgressBar optional; Button optional)
 * @version  1.6.2
 */(function(a){a.widget("db.jWizard",{_stepIndex:0,_stepCount:0,_actualCount:0,_create:function(){this._buildSteps(),this._buildTitle(),this.options.menuEnable&&this._buildMenu(),this._buildButtons(),this.options.counter.enable&&this._buildCounter(),this.element.addClass("ui-widget jw-widget"),this.element.find(".ui-state-default").on("mouseover mouseout",function(b){b.type==="mouseover"?a(this).addClass("ui-state-hover"):a(this).removeClass("ui-state-hover")}),this._changeStep(this._stepIndex,!0)},destroy:function(){this._destroySteps(),this._destroyTitle(),this.options.menuEnable&&this._destroyMenu(),this._destroyButtons(),this.options.counter.enable&&this._destroyCounter(),this.element.removeClass("ui-widget"),this.element.find(".ui-state-default").unbind("mouseover").unbind("mouseout"),a.Widget.prototype.destroy.call(this)},disable:function(){this.element.addClass("ui-state-disabled").find("button").attr("disabled","disabled")},enable:function(){this.element.removeClass("ui-state-disabled").find("button").removeAttr("disabled")},_setOption:function(a,b){var c=a.split(".");if(c.length>1)switch(c[0]){case"buttons":this.options[c[0]][c[1]]=b;switch(c[1]){case"jqueryui":this.options[c[0]][c[1]][c[2]]=b;if(c[2]==="enable"){b?this.find(".jw-buttons > button").button("destroy"):(this._destroyButtons(),this._buildButtons());break}break;case"cancelHide":this.element.find(".jw-button-cancel")[b?"addClass":"removeClass"]("ui-helper-hidden");break;case"cancelType":this.element.find(".jw-button-cancel").attr("type",b);break;case"finishType":this.element.find(".jw-button-finish").attr("type",b);break;case"cancelText":this.element.find(".jw-button-cancel").text(b);break;case"previousText":this.element.find(".jw-button-previous").text(b);break;case"nextText":this.element.find(".jw-button-next").text(b);break;case"finishText":this.element.find(".jw-button-finish").text(b)}break;case"counter":this.options[c[0]][c[1]]=b;switch(c[1]){case"enable":b?(this._buildCounter(),this._updateCounter()):this._destroyCounter();break;case"type":case"progressbar":case"location":this.options.counter.enable&&(this._destroyCounter(),this._buildCounter(),this._updateCounter());break;case"startCount":case"startHide":case"finishCount":case"finishHide":case"appendText":case"orientText":this.options.counter.enable&&this._updateCounter()}break;case"effects":c.length===2?this.options[c[0]][c[1]]=b:this.options[c[0]][c[1]][c[2]]=b}else{this.options[c[0]]=b;switch(c[0]){case"titleHide":this.element.find(".jw-header")[b?"addClass":"removeClass"]("ui-helper-hidden");break;case"menuEnable":b?(this._buildMenu(),this._updateMenu()):this._destroyMenu();break;case"counter":this._destroyCounter(),this._buildCounter(),this._updateCounter()}}},firstStep:function(){this.changeStep(0,"first")},lastStep:function(){this.changeStep(this._stepCount-1,"last")},nextStep:function(){var a={wizard:this.element,currentStepIndex:this._stepIndex,nextStepIndex:this._stepIndex+1,delta:1};this._trigger("next",null,a)!==!1&&this.changeStep(this._stepIndex+1,"next")},previousStep:function(){var a={wizard:this.element,currentStepIndex:this._stepIndex,nextStepIndex:this._stepIndex-1,delta:-1};this._trigger("previous",null,a)!==!1&&this.changeStep(this._stepIndex-1,"previous")},changeStep:function(b,c){c=c||"manual",b=typeof b=="number"?b:a(b).index();var d={wizard:this.element,currentStepIndex:this._stepIndex,nextStepIndex:b,delta:b-this._stepIndex,type:c};this._trigger("changestep",null,d)!==!1&&this._changeStep(b)},_effect:function(b,c,d,e,f){var g=this,h=this.options.effects[c][d];e=e||"effect";if(!b.length||!b.hasClass("jw-animated")){b[e](),f&&f.call(this);return!1}h.callback=f||a.noop,b[e](h.type,h.options,h.duration,h.callback)},_log:function(){this.options.debug&&window.console&&console.log[console.firebug?"apply":"call"](console,Array.prototype.slice.call(arguments))},_updateNavigation:function(a){this._updateButtons(),this.options.menuEnable&&this._updateMenu(a),this.options.counter.enable&&this._updateCounter(a)},_buildTitle:function(){this.element.prepend(a("<div />",{"class":"jw-header ui-widget-header ui-corner-top"+(this.options.hideTitle?" ui-helper-hidden":""),html:'<h2 class="jw-title'+(this.options.effects.enable||this.options.effects.title.enable?" jw-animated":"")+'" />'}))},_updateTitle:function(a){var b=this,c=this.element.find(".jw-title"),d=this.element.find(".jw-step:eq("+this._stepIndex+")");a?c.text(d.attr("title")):this._effect(c,"title","hide","hide",function(){c.text(d.attr("title")),b._effect(c,"title","show","show")})},_destroyTitle:function(){a(".jw-header").remove()},_buildSteps:function(){var b=this.element.children("div, fieldset");this._stepCount=b.length,b.addClass("jw-step").each(function(b){var c=a(this);this.tagName.toLowerCase()==="fieldset"&&c.attr("title",c.find("legend").text())}),(this.options.effects.enable||this.options.effects.step.enable)&&b.addClass("jw-animated"),b.hide().wrapAll(a("<div />",{"class":"jw-content ui-widget-content ui-helper-clearfix",html:'<div class="jw-steps-wrap" />'})).eq(this._stepIndex).show()},_destroySteps:function(){a(".jw-step").show().unwrap().unwrap(),a(".jw-step").unbind("show").unbind("hide").removeClass("jw-step")},_changeStep:function(a,b){var c=this,d=this.element.find(".jw-step"),e=d.eq(this._stepIndex);if(typeof a=="number"){if(a<0||a>d.length-1){alert("Index "+a+" Out of Range");return!1}a=d.eq(a)}else if(typeof a=="object"&&!a.is(d.selector)){alert("Supplied Element is NOT one of the Wizard Steps");return!1}b?(this._stepIndex=d.index(a),this._updateTitle(b),this._updateNavigation(b)):(this._disableButtons(),this._stepIndex=d.index(a),this._updateTitle(b),this._effect(e,"step","hide","hide",function(){c._effect(a,"step","show","show",function(){c._enableButtons(),c._updateNavigation(b)})}))},_buildMenu:function(){var b=[],c,d;this.element.addClass("jw-hasmenu"),this.element.find(".jw-step").each(function(c){b.push(a("<li />",{"class":"ui-corner-all "+(c===0?"jw-current ui-state-highlight":"jw-inactive ui-state-disabled"),html:a("<a />",{step:c,text:a(this).attr("title")})})[0])}),c=a("<div />",{"class":"jw-menu-wrap",html:a("<div />",{"class":"jw-menu",html:a("<ol />",{html:a(b)})})}),this.element.find(".jw-content").prepend(c),(this.options.effects.enable||this.options.effects.menu.enable)&&c.find("li").addClass("jw-animated"),c.find("a").click(a.proxy(function(b){var c=a(b.target),d=parseInt(c.attr("step"),10);c.parent().hasClass("jw-active")&&this.changeStep(d,d<=this._stepIndex?"previous":"next")},this))},_destroyMenu:function(){this.element.removeClass("jw-hasmenu").find(".jw-menu-wrap").remove()},_updateMenu:function(b){var c=this,d=this._stepIndex,e=this.element.find(".jw-menu");b||this._effect(e.find("li:eq("+d+")"),"menu","change"),e.find("a").each(function(b){var c=a(this),e=c.parent(),f=parseInt(c.attr("step"),10),g="";f<d?g+="jw-active ui-state-default":f===d?g+="jw-current ui-state-highlight":f>d&&(g+="jw-inactive ui-state-disabled",c.removeAttr("href")),e.removeClass("jw-active jw-current jw-inactive ui-state-default ui-state-highlight ui-state-disabled").addClass(g)})},_buildCounter:function(){var b=a("<span />",{"class":"jw-counter ui-widget-content ui-corner-all jw-"+this.options.counter.orientText});this.options.counter.location==="header"?this.element.find(".jw-header").prepend(b):this.options.counter.location==="footer"&&this.element.find(".jw-footer").prepend(b),this.options.counter.startCount||this._stepCount--,this.options.counter.finishCount||this._stepCount--,(this.options.effects.enable||this.options.effects.counter.enable)&&b.addClass("jw-animated"),this.options.counter.progressbar&&b.html('<span class="jw-counter-text" /> <span class="jw-counter-progressbar" />').find(".jw-counter-progressbar").progressbar()},_updateCounter:function(a){var b=this.element.find(".jw-counter"),c=this.options.counter,d="",e=this._stepIndex,f=this._stepCount,g=0;c.startCount||(e--,f--),a||this._effect(b,"counter","change"),g=Math.round(e/f*100),c.type==="percentage"?d=(g<=100?g:100)+"%":c.type==="count"?(e<0?d=0:e>f?d=f:d=e,d+=" of "+f):d="N/A",c.appendText&&(d+=" "+c.appendText),c.progressbar?(this.element.find(".jw-counter-progressbar").progressbar("option","value",g),this.element.find(".jw-counter-text").text(d)):b.text(d),c.startHide&&this._stepIndex===0||c.finishHide&&this._stepIndex===this._actualCount-1?b.hide():b.show()},_destroyCounter:function(){this.element.find(".jw-counter").remove()},_buildButtons:function(){var b=this,c=this.options.buttons,d=a("<div />",{"class":"jw-footer ui-widget-header ui-corner-bottom"}),e=a('<button type="'+c.cancelType+'" class="ui-state-default ui-corner-all jw-button-cancel jw-priority-secondary"'+(c.cancelHide?" ui-helper-hidden":"")+'">'+c.cancelText+"</button>"),f=a('<button type="button" class="ui-state-default ui-corner-all jw-button-previous">'+c.previousText+"</button>"),g=a('<button type="button" class="ui-state-default ui-corner-all jw-button-next">'+c.nextText+"</button>"),h=a('<button type="'+c.finishType+'" class="ui-state-default ui-corner-all jw-button-finish ui-state-highlight">'+c.finishText+"</button>");e.click(function(a){b._trigger("cancel",a)}),f.click(function(a){b.previousStep()}),g.click(function(a){b.nextStep()}),h.click(function(a){b._trigger("finish",a)}),c.jqueryui.enable&&(e.button({icons:{primary:c.jqueryui.cancelIcon}}),f.button({icons:{primary:c.jqueryui.previousIcon}}),g.button({icons:{secondary:c.jqueryui.nextIcon}}),h.button({icons:{secondary:c.jqueryui.finishIcon}})),this.element.append(d.append(a('<div class="jw-buttons" />').append(e).append(f).append(g).append(h)))},_updateButtons:function(){var a=this.element.find(".jw-step"),b=this.element.find(".jw-button-previous"),c=this.element.find(".jw-button-next"),d=this.element.find(".jw-button-finish");switch(a.index(a.filter(":visible"))){case 0:b.hide(),c.show(),d.hide();break;case a.length-1:b.show(),c.hide(),d.show();break;default:b.show(),c.show(),d.hide()}},_disableButtons:function(){this.element.find(".jw-buttons button").addClass("ui-state-disabled").attr("disabled",!0)},_enableButtons:function(){this.element.find(".jw-buttons button").removeClass("ui-state-disabled").attr("disabled",!1)},_destroyButtons:function(){this.element.find(".jw-footer").remove()},options:{debug:!1,disabled:!1,titleHide:!1,menuEnable:!1,buttons:{jqueryui:{enable:!1,cancelIcon:"ui-icon-circle-close",previousIcon:"ui-icon-circle-triangle-w",nextIcon:"ui-icon-circle-triangle-e",finishIcon:"ui-icon-circle-check"},cancelHide:!1,cancelType:"button",finishType:"button",cancelText:"Cancel",previousText:"Previous",nextText:"Next",finishText:"Finish"},counter:{enable:!1,type:"count",progressbar:!1,location:"footer",startCount:!0,startHide:!1,finishCount:!0,finishHide:!1,appendText:"Complete",orientText:"left"},effects:{enable:!1,step:{enable:!1,hide:{type:"slide",options:{direction:"left"},duration:"fast"},show:{type:"slide",options:{direction:"left"},duration:"fast"}},title:{enable:!1,hide:{type:"slide",duration:"fast"},show:{type:"slide",duration:"fast"}},menu:{enable:!1,change:{type:"highlight",duration:"fast"}},counter:{enable:!1,change:{type:"highlight",duration:"fast"}}},cancel:a.noop,previous:a.noop,next:a.noop,finish:a.noop,changestep:function(a,b){if(a.isDefaultPrevented()&&typeof a.nextStepIndex!="undefined"){b.wizard.jWizard("changeStep",a.nextStepIndex);return!1}}}})})(jQuery)