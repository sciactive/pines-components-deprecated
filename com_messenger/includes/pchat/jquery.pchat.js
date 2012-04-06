/*
 * jQuery Pines Chat (pchat) Plugin 0.10.2dev
 *
 * http://pinesframework.org/pchat/
 * Copyright (c) 2012 Hunter Perrin
 *
 * Triple license under the GPL, LGPL, and MPL:
 *	  http://www.gnu.org/licenses/gpl.html
 *	  http://www.gnu.org/licenses/lgpl.html
 *	  http://www.mozilla.org/MPL/MPL-1.1.html
 */

// First we need to emulate localStorage if it doesn't exist.
// Taken from https://developer.mozilla.org/en/DOM/Storage
if (!window.localStorage) {
	Object.defineProperty(window, "localStorage", new (function () {
	var aKeys = [], oStorage = {};
	Object.defineProperty(oStorage, "getItem", {
		value: function (sKey) {return sKey ? this[sKey] : null;},
		writable: false,
		configurable: false,
		enumerable: false
	});
	Object.defineProperty(oStorage, "key", {
		value: function (nKeyId) {return aKeys[nKeyId];},
		writable: false,
		configurable: false,
		enumerable: false
	});
	Object.defineProperty(oStorage, "setItem", {
		value: function (sKey, sValue) {
		if(!sKey) {return;}
		document.cookie = escape(sKey) + "=" + escape(sValue) + "; path=/";
		},
		writable: false,
		configurable: false,
		enumerable: false
	});
	Object.defineProperty(oStorage, "length", {
		get: function () {return aKeys.length;},
		configurable: false,
		enumerable: false
	});
	Object.defineProperty(oStorage, "removeItem", {
		value: function (sKey) {
		if(!sKey) {return;}
		var sExpDate = new Date();
		sExpDate.setDate(sExpDate.getDate() - 1);
		document.cookie = escape(sKey) + "=; expires=" + sExpDate.toGMTString() + "; path=/";
		},
		writable: false,
		configurable: false,
		enumerable: false
	});
	this.get = function () {
		var iThisIndx;
		for (var sKey in oStorage) {
		iThisIndx = aKeys.indexOf(sKey);
		if (iThisIndx === -1) {oStorage.setItem(sKey, oStorage[sKey]);}
		else {aKeys.splice(iThisIndx, 1);}
		delete oStorage[sKey];
		}
		for (aKeys; aKeys.length > 0; aKeys.splice(0, 1)) {oStorage.removeItem(aKeys[0]);}
		for (var iCouple, iKey, iCouplId = 0, aCouples = document.cookie.split(/\s*;\s*/); iCouplId < aCouples.length; iCouplId++) {
		iCouple = aCouples[iCouplId].split(/\s*=\s*/);
		if (iCouple.length > 1) {
			oStorage[iKey = unescape(iCouple[0])] = unescape(iCouple[1]);
			aKeys.push(iKey);
		}
		}
		return oStorage;
	};
	this.configurable = false;
	this.enumerable = true;
	})());
}

(function($){
	$.fn.pchat = function(options) {
		// Build main options before element iteration.
		var opts = $.extend({}, $.fn.pchat.defaults, options);

		// Iterate and transform each matched element.
		var all_elements = this;
		all_elements.each(function(){
			var pchat = $(this),
				attaching_from_storage = true; // Remember if we're attaching from localStorage, in case of error.
			// Check for our required libraries.
			if (!Strophe || !JSON) return false;
			// Check for the pchat class. If it has it, we've already transformed this element.
			if (pchat.hasClass("ui-pchat")) return true;

			pchat.pchat_version = "0.10.2dev";

			// Add the pchat class.
			pchat.addClass("ui-pchat");
			// Import the options.
			pchat.extend(pchat, opts);
			// An object for open conversation windows.
			pchat.pchat_conversations = {};

			// Add widget classes.
			if (pchat.pchat_widget_box)
				pchat.addClass("ui-pchat-widget ui-widget ui-widget-content ui-corner-all");

			// Set up the user interface.
			if (pchat.pchat_title)
				$('<div class="ui-pchat-title ui-widget-header ui-corner-all"></div>').html(pchat.pchat_title).appendTo(pchat);

			var roster_elem = $('<div class="ui-pchat-roster"></div>').appendTo(pchat);

			var presence_text = {
				"working": '<i class="'+pchat.pchat_presence_icons.working+'"></i>Updating Status...',
				"available": '<i class="'+pchat.pchat_presence_icons.available+'"></i>Available',
				"chat": '<i class="'+pchat.pchat_presence_icons.available+'"></i>Chatty',
				"away": '<i class="'+pchat.pchat_presence_icons.away+'"></i>Away',
				"xa": '<i class="'+pchat.pchat_presence_icons.away_extended+'"></i>Extended Away',
				"dnd": '<i class="'+pchat.pchat_presence_icons.busy+'"></i>Busy',
				"offline": '<i class="'+pchat.pchat_presence_icons.offline+'"></i>Offline',
				"disconnected": '<i class="'+pchat.pchat_presence_icons.offline+'"></i>Not Connected'
			};
			var action_bar = $('<div class="ui-pchat-action-bar ui-helper-clearfix"></div>').appendTo(pchat);
			// Presence state dropdown:
			action_bar.append($('<div class="ui-pchat-presence-menu btn-group dropup"><a class="btn dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);"><span class="ui-pchat-presence-current">'+presence_text.disconnected+'</span>&nbsp;<span class="caret"></span></a><ul class="dropdown-menu"></ul></div>'));
			var presence_current = action_bar.find(".ui-pchat-presence-current");
			var presence_menu = action_bar.find(".ui-pchat-presence-menu .dropdown-menu");
			var login_button = $('<li><a href="javascript:void(0);">Login</a></li>').on("click", "a", function(){
				pchat.pchat_connect();
			});
			var logout_button = $('<li><a href="javascript:void(0);">Logout</a></li>').on("click", "a", function(){
				attaching_from_storage = false;
				pchat.pchat_disconnect();
			});
			presence_menu
			.append($('<li><a href="javascript:void(0);">'+presence_text.available+'</a></li>').on("click", "a", function(){
				pchat.pchat_set_presence("available", localStorage.getItem("pchat-presence-status"));
			}))
			.append($('<li><a href="javascript:void(0);">'+presence_text.chat+'</a></li>').on("click", "a", function(){
				pchat.pchat_set_presence("chat", localStorage.getItem("pchat-presence-status"));
			}))
			.append($('<li><a href="javascript:void(0);">'+presence_text.away+'</a></li>').on("click", "a", function(){
				pchat.pchat_set_presence("away", localStorage.getItem("pchat-presence-status"));
			}))
			.append($('<li><a href="javascript:void(0);">'+presence_text.xa+'</a></li>').on("click", "a", function(){
				pchat.pchat_set_presence("xa", localStorage.getItem("pchat-presence-status"));
			}))
			.append($('<li><a href="javascript:void(0);">'+presence_text.dnd+'</li>').on("click", "a", function(){
				pchat.pchat_set_presence("dnd", localStorage.getItem("pchat-presence-status"));
			}))
			.append($('<li><a href="javascript:void(0);">'+presence_text.offline+'</a></li>').on("click", "a", function(){
				pchat.pchat_set_presence("offline", localStorage.getItem("pchat-presence-status"));
			}))
			.append($('<li class="divider"></li>'))
			.append(login_button)
			.append(logout_button);
			// Status input:
			action_bar.append($('<label class="ui-pchat-status-container">Status:<input type="text" size="15" class="ui-pchat-status" /></label>'));
			var presence_status = action_bar.find(".ui-pchat-status");
			presence_status.change(function(){
				pchat.pchat_set_presence(localStorage.getItem("pchat-presence"), presence_status.val());
			}).keypress(function(e){
				if (e.keyCode == 13)
					$(this).change();
			});
			// Main menu:
			action_bar.append($('<div class="ui-pchat-main-menu btn-group dropup pull-right"><a class="btn dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);"><span class="caret"></span></a><ul class="dropdown-menu"></ul></div>'));
			var main_menu = action_bar.find(".ui-pchat-main-menu .dropdown-menu");
			main_menu
			.append($('<li><a href="javascript:void(0);"><i class="icon-plus"></i>Add a Contact</a></li>').on("click", "a", function(){
				var form = $('<div title="Add a Contact"><div class="pf-form"></div></div>').find(".pf-form")
				.append('<div class="pf-element"><label><span class="pf-label">Username</span><input class="pf-field" type="text" name="username" /></label></div>')
				.find('input[name=username]').change(function(){
					var field = $(this);
					var cur_val = field.val();
					if (!cur_val.match(/@.*$/))
						field.val(cur_val+"@"+pchat.pchat_domain);
				}).end()
				.append('<div class="pf-element"><label><span class="pf-label">Alias (Optional)</span><input class="pf-field" type="text" name="alias" /></label></div>')
				//.append('<div class="pf-element"><label><span class="pf-label">Message (Optional)</span><span class="pf-note">Your contact will see this when they receive your request.</span><input class="pf-field" type="text" name="message" /></label></div>')
				.end().append('<br/>')
				.dialog({
					modal: true,
					autoOpen: true,
					width: 500,
					close: function(){
						$(this).dialog("destroy").remove();
					},
					buttons: {
						"Cancel": function(){
							$(this).dialog("close");
						},
						"Send Request": function(){
							// Get the new contact details.
							var jid = form.find('input[name=username]').val();
							var alias = form.find('input[name=alias]').val();
							//var message = form.find('input[name=message]').val();
							// Verify them.
							jid = Strophe.getBareJidFromJid(jid);
							var node = Strophe.getNodeFromJid(jid);
							var domain = Strophe.getDomainFromJid(jid);
							if (!node || node == "" || !domain || domain == "") {
								alert("Please provide a valid contact username.");
								return;
							}
							pchat.pchat_add_contact(jid, alias);
							$(this).dialog("close");
						}
					}
				});
			}));
			if (pchat.pchat_sounds) {
				if (!soundManager) {
					pchat.pchat_sound = false;
					if (pchat.pchat_sound)
						log("ERROR: Sound is enabled, but the soundManager library was not found!");
				} else {
					pchat.SMSounds = {};
					soundManager.onready(function(){
						$.each(pchat.pchat_sounds, function(sound, files){
							pchat.SMSounds[sound] = soundManager.createSound({
								id: 'pchat-'+sound,
								url: files
							});
						});
					});
				}
				// Remember a saved sound preference.
				var prev_sound_setting = localStorage.getItem("pchat-sounds");
				if (prev_sound_setting === "true")
					pchat.pchat_sound = true;
				if (prev_sound_setting === "false")
					pchat.pchat_sound = false;
				// A button to disable/enable sounds.
				main_menu.append($('<li><a href="javascript:void(0);">'+(pchat.pchat_sound ? '<i class="icon-volume-off"></i>Mute' : '<i class="icon-volume-up"></i>Unmute')+'</a></li>').on("click", "a", function(){
					pchat.pchat_sound = !pchat.pchat_sound;
					$(this).html(pchat.pchat_sound ? '<i class="icon-volume-off"></i>Mute' : '<i class="icon-volume-up"></i>Unmute');
					localStorage.setItem("pchat-sounds", pchat.pchat_sound ? "true" : "false");
				}));
			}
			// The log function only does anything if the log is enabled.
			var log;
			if (pchat.pchat_show_log) {
				var log_elem = $('<div class="ui-pchat-log"></div>').appendTo(pchat);
				log = function(msg, escape){
					log_elem.append('<br />').append(escape === false ? msg : document.createTextNode(msg)).scrollTop(999999);
				};
			} else
				log = function(){};

			// Chat code.
			Strophe.addNamespace('TIME', "urn:xmpp:time");
			var connection = null;
			var save_rid = function(){
				log('Saved RID.');
				// Save the new RID for reattachment.
				localStorage.setItem("pchat-rid", connection.rid);
				return true;
			};
			var save_state = function(){
				log('Saved state.');
				// Save the state of the session.
				// Save the roster.
				localStorage.setItem("pchat-roster", JSON.stringify(connection.roster.items));
				localStorage.setItem("pchat-rosterver", JSON.stringify(connection.roster.ver));
				// Save conversations.
				localStorage.setItem("pchat-conversations", JSON.stringify(pchat.pchat_conversations));
				return true;
			};
			// Save the RID when the page is unloading.
			window.onunload = save_rid;
			var handlers = {
				onConnect: function(status){
					switch (status) {
						case Strophe.Status.ERROR:
							log('An error occurred.');
							break;
						case Strophe.Status.CONNECTING:
							log('Connecting.');
							login_button.addClass("disabled");
							break;
						case Strophe.Status.CONNFAIL:
							log('Failed to connect.');
							login_button.removeClass("disabled");
							break;
						case Strophe.Status.AUTHENTICATING:
							log('Authenticating.');
							break;
						case Strophe.Status.AUTHFAIL:
							log('Failed to authenticate.');
							login_button.removeClass("disabled");
							break;
						case Strophe.Status.ATTACHED:
							log('Attached.');
						case Strophe.Status.CONNECTED:
							if (status == Strophe.Status.CONNECTED)
								log('Connected.');
							login_button.hide().removeClass("disabled");
							logout_button.show().removeClass("disabled");
							localStorage.setItem("pchat-jid", connection.jid);
							localStorage.setItem("pchat-sid", connection.sid);
							localStorage.setItem("pchat-rid", connection.rid);

							// Add handlers connection.addHandler(callback, namespace, stanza_name, type, id, from)
							connection.addHandler(save_rid);
							connection.addHandler(handlers.onMessage, null, 'message');
							connection.addHandler(handlers.onPresence, null, 'presence');
							// TODO: Is this handler needed?
							//connection.addHandler(handlers.onIQ, null, 'iq');

							connection.roster.registerCallback(handlers.onRoster);
							// This handler isn't needed now with the roster plugin.
							//connection.addHandler(handlers.onIQRoster, Strophe.NS.ROSTER, 'iq');
							connection.addHandler(handlers.onIQVersion, Strophe.NS.VERSION, 'iq');
							connection.addHandler(handlers.onIQTime, Strophe.NS.TIME, 'iq');

							if (status == Strophe.Status.CONNECTED) {
								// This is the initial connection, so we need to request roster and send initial presence.
								connection.roster.get(function(){
									// Since this is initial sign-on, send initial presence.
									connection.send($pres().tree());
									presence_current.html(presence_text.available);
								});
							} else {
								// This is an attached session, so request roster changes using the stored roster.
								var roster = localStorage.getItem("pchat-roster");
								var ver = localStorage.getItem("pchat-rosterver");
								var presence = localStorage.getItem("pchat-presence");
								var pres_stat = localStorage.getItem("pchat-presence-status");
								var conversations = localStorage.getItem("pchat-conversations");
								presence_current.html(presence_text[presence]);
								presence_status.val(pres_stat);
								if (roster) {
									log("Loading Saved Roster: "+roster);
									roster = JSON.parse(roster);
									ver = JSON.parse(ver);
									// Put the recovered roster back into the roster plugin.
									connection.roster.items = roster;
									connection.roster.ver = ver;
									// Call the roster handler.
									handlers.onRoster(roster);
								}
								// Get roster changes, if supported.
								if (connection.roster.supportVersioning())
									connection.roster.get(null, ver, roster);
								// Rebuild conversations.
								if (conversations) {
									log("Loading Saved Conversations: "+conversations);
									conversations = JSON.parse(conversations);
									$.each(conversations, function(i, convo){
										var new_convo = get_conv(i);
										if (convo.minimized)
											new_convo.element.find(".ui-pchat-controls .ui-icon-circle-minus").click();
										$.each(convo.messages, function(i, msg){
											pchat.pchat_display_message(msg, true);
										});
										new_convo.element.find(".ui-pchat-messages").scrollTop(999999);
										save_state();
									});
								}
							}
							break;
						case Strophe.Status.DISCONNECTING:
							log('Disconnecting.');
							logout_button.addClass("disabled");
							break;
						case Strophe.Status.DISCONNECTED:
							log('Disconnected.');
							logout_button.hide().removeClass("disabled");
							login_button.show().removeClass("disabled");
							presence_current.html(presence_text.disconnected);
							if (localStorage.getItem("pchat-sid")) {
								localStorage.removeItem("pchat-jid");
								localStorage.removeItem("pchat-sid");
								localStorage.removeItem("pchat-rid");
								localStorage.removeItem("pchat-roster");
								localStorage.removeItem("pchat-rosterver");
								localStorage.removeItem("pchat-conversations");
								// If we tried to attach from localStorage and failed, try starting a new connection.
								if (attaching_from_storage) {
									attaching_from_storage = false;
									pchat.pchat_connect();
								}
							}
							roster_elem.empty();
							break;
					}
				},
				onRoster: function(roster, item){
					// Save the roster.
					save_state();
					console.log("Roster Element:");
					console.log(roster);
					console.log("Presence Element:");
					console.log(item);
					$.each(roster, function(i, contact){
						var contact_elem = roster_elem.find(".ui-pchat-contact[data-jid=\""+Strophe.xmlescape(contact.jid)+"\"]");
						// Remove a contact from the roster when they are deleted.
						if (contact.subscription === "remove") {
							if (contact_elem.length)
								contact_elem.remove();
							return;
						}
						var contact_display, contact_nav;
						if (contact_elem.length) {
							contact_display = contact_elem.find(".brand").empty();
							contact_nav = contact_elem.find(".nav-collapse").empty();
						} else {
							contact_elem = $('<div class="ui-pchat-contact navbar"><div class="navbar-inner"><div class="container"><a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></a></div></div></div>').attr("data-jid", Strophe.xmlescape(contact.jid)).appendTo(roster_elem);
							contact_display = $('<a class="brand" href="javascript:void(0);"></a>').appendTo(contact_elem.find(".container")).click(function(){
								var cw = get_conv(contact.jid);
								cw.element.find("textarea").focus().select();
							});
							contact_nav = $('<div class="nav-collapse"></div>').appendTo(contact_elem.find(".container"));
						}
						// Remember that this contact was added by us, so we can automatically authorize them.
						if (contact.ask == "subscribe")
							contact_elem.attr("data-authorize", "true");
						var contact_main_bar = $('<ul class="nav"></ul>').appendTo(contact_nav);
						var contact_right = $('<ul class="nav pull-right"><li class="divider-vertical"></li><li class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" href="javascript:void(0);">Edit <b class="caret"></b></a><ul class="dropdown-menu"></ul></li></ul>').appendTo(contact_nav);
						var contact_menu = contact_right.find(".dropdown-menu");
						var presence = {show: "offline"};
						// Get the highest priority or most important presence.
						var resource_arr = $.map(contact.resources, function(e){return e;});
						if (resource_arr.length)
							presence = resource_arr.sort(function(a, b){
								if (!a)
									return 1;
								if (!b)
									return -1;
								if ((a.priority && a.priority != "") || (b.priority && b.priority != ""))
									return Number(b.priority) - Number(a.priority);
								if (a.show == "chat")
									return -1;
								if (b.show == "chat")
									return 1;
								if (a.show == "dnd")
									return -1;
								if (b.show == "dnd")
									return 1;
								if (!a.show || a.show == "")
									return -1;
								if (!b.show || b.show == "")
									return 1;
								if (a.show == "away")
									return -1;
								if (b.show == "away")
									return 1;
								if (a.show == "xa")
									return -1;
								if (b.show == "xa")
									return 1;
								return 0;
							})[0];
						console.log("Calculated Presence:");
						console.log(presence);
						var icon_class = pchat.pchat_presence_icons.offline;
						var cur_status = "offline";
						if (contact.subscription == "to" || contact.subscription == "both") {
							switch (presence.show) {
								case "":
								case "chat":
									icon_class = pchat.pchat_presence_icons.available;
									cur_status = "online";
									break;
								case "dnd":
									icon_class = pchat.pchat_presence_icons.busy;
									cur_status = "online";
									break;
								case "away":
									icon_class = pchat.pchat_presence_icons.away;
									cur_status = "online";
									break;
								case "xa":
									icon_class = pchat.pchat_presence_icons.away_extended;
									cur_status = "online";
									break;
							}
						}
						// Play a sound when a contact changes online state.
						var prev_status = contact_elem.attr("data-prev-status");
						if ((prev_status == "offline" && cur_status == "online") || (prev_status == "online" && cur_status == "offline"))
							pchat.pchat_play_sound(cur_status);
						contact_elem.attr("data-prev-status", cur_status);
						// Name, presence, status.
						contact_display.append($('<span class="ui-pchat-contact-presence">&nbsp;</span>').addClass(icon_class))
						.append($('<span class="ui-pchat-contact-name"></span>').text((contact.name && contact.name != "") ? contact.name : contact.jid));
						if (contact.subscription == "both") {
							if (presence.status && presence.status !== "")
								contact_main_bar.append($('<li class="ui-pchat-contact-status"><a href="javascript:void(0);"></a></li>').children().html(Strophe.xmlescape(presence.status).replace(/&amp;([a-z]+);/g, "&$1;")).end());
						} else if (contact.ask == "subscribe")
							contact_right.prepend($('<li class="ui-pchat-contact-status"><a href="javascript:void(0);">Awaiting Approval</a></li>'));
						else
							contact_right.prepend($('<li class="ui-pchat-contact-status"><a href="javascript:void(0);">Not Authorized</a></li>'));
						// Menu items.
						contact_menu.append($('<li><a href="javascript:void(0);">Alias</a></li>').on("click", "a", function(){
							var name_box = contact_display.find(".ui-pchat-contact-name");
							var cur_content = name_box.html();
							name_box.empty();
							var save = function(name){
								name_box.html("Saving...");
								connection.roster.update(contact.jid, name, contact.groups);
							};
							$('<input type="text" />').val(contact.name).keypress(function(e){
								if (e.keyCode == 13)
									save($(this).val());
								else if (e.keyCode == 27) {
									$(this).remove();
									name_box.html(cur_content);
								}
							}).blur(function(){
								save($(this).val());
							}).appendTo(name_box).focus().select();
						}));
						// If they have granted access to us.
						if (contact.subscription == "to" || contact.subscription == "both")
							contact_menu.append($('<li><a href="javascript:void(0);" title="No longer receive their status.">Unsubscribe</a></li>').on("click", "a", function(){
								connection.roster.unsubscribe(contact.jid);
							}));
						// If we have granted access to them.
						if (contact.subscription == "from" || contact.subscription == "both")
							contact_menu.append($('<li><a href="javascript:void(0);" title="Disallow them from seeing your status.">Unauthorize</a></li>').on("click", "a", function(){
								connection.roster.unauthorize(contact.jid);
							}));
						// If they haven't granted access to us.
						if (contact.subscription == "from" || contact.subscription == "none")
							contact_menu.append($('<li><a href="javascript:void(0);" title="Ask contact for permission to see their status.">Request Authorization</a></li>').on("click", "a", function(){
								connection.roster.subscribe(contact.jid);
							}));
						// If we haven't granted access to them.
						if (contact.subscription == "to" || contact.subscription == "none")
							contact_menu.append($('<li><a href="javascript:void(0);" title="Give contact permission to see your status.">Grant Authorization</a></li>').on("click", "a", function(){
								connection.roster.authorize(contact.jid);
							}));
						contact_menu.append($('<li class="divider"></li>'))
						.append($('<li><a href="javascript:void(0);">Block</a></li>').on("click", "a", function(){
							// TODO: Implement Block User.
						}))
						.append($('<li><a href="javascript:void(0);" title="Unsubscribe from this contact and remove them from your roster.">Remove</a></li>').on("click", "a", function(){
							if (!confirm('Are you sure you want to remove the contact '+contact.jid+' ('+contact.name+') from your contact list?'))
								return;
							pchat.pchat_remove_contact(contact.jid);
						}));
						// Now update any open chat windows.
						if (pchat.pchat_conversations[contact.jid] && pchat.pchat_conversations[contact.jid].element) {
							pchat.pchat_conversations[contact.jid].element.find(".ui-pchat-header .ui-pchat-conversation-title").html(contact_display.html());
							if (prev_status == "online" && cur_status == "offline")
								pchat.pchat_display_notice({
									jid: contact.jid,
									classes: "status-offline",
									content: Strophe.xmlescape(contact.jid)+" has gone offline. You can still send messages, but they might not be delivered."
								});
							else if (cur_status != "offline")
								pchat.pchat_conversations[contact.jid].element.find(".ui-pchat-messages .ui-pchat-notice.status-offline").remove();
						}
					});
					return true;
				},
				onMessage: function(msg){
					var to = msg.getAttribute('to');
					var from = msg.getAttribute('from');
					var type = msg.getAttribute('type');
					var elems = msg.getElementsByTagName('body');

					if (type == "chat" && elems.length > 0) {
						var body = elems[0];
						var from_alias = connection.roster.findItem(Strophe.getBareJidFromJid(from));
						if (from_alias)
							from_alias = (from_alias.name && from_alias.name != "") ? from_alias.name : from_alias.jid;
						else
							from_alias = Strophe.getBareJidFromJid(from);
						pchat.pchat_display_message({
							from_jid: from,
							from_alias: from_alias,
							to_jid: to,
							to_alias: "Me",
							date: new Date().getTime(),
							content: $(body).text()
						});

//						log('ECHOBOT: I got a message from ' + from + ': ' + Strophe.getText(body));
//
//						var reply = $msg({to: from, from: to, type: 'chat'}).cnode(Strophe.copyElement(body));
//						connection.send(reply.tree());
//
//						log('ECHOBOT: I sent ' + from + ': ' + Strophe.getText(body));
					}

					// Handlers always must return true to stay active.
					return true;
				},
				onPresence: function(presence){
					// Attaching worked!
					attaching_from_storage = false;
					var jpres = $(presence);
					if (presence.getAttribute('type') == 'error') {
						if (jpres.children("error").attr("type") == "cancel") {
							var from = Strophe.getBareJidFromJid(presence.getAttribute('from'));
							var roster_entry = roster_elem.find(".ui-pchat-contact[data-jid=\""+Strophe.xmlescape(from)+"\"]");
							if (roster_entry.length)
								alert("An error occured trying to contact "+from+". Error: "+jpres.children("error").children().prop("tagName"));
						}
					}
					// Check for our own presence and update the current presence display.
					if (presence.getAttribute('from') == connection.jid) {
						// Find the presence state.
						var type = presence.getAttribute('type');
						if (type == "unavailable") {
							localStorage.setItem("pchat-presence", "offline");
							presence_current.html(presence_text.offline);
						} else {
							var show = jpres.children("show");
							if (show.length) {
								show = show.text();
								localStorage.setItem("pchat-presence", show);
								presence_current.html(presence_text[show]);
							} else {
								localStorage.setItem("pchat-presence", "available");
								presence_current.html(presence_text.available);
							}
						}
						// And the status.
						var status = jpres.children("status").eq(0);
						if (status.length)
							presence_status.val(status.text());
					} else {
						// Approve subscription requests from contacts we've added.
						var jid = Strophe.getBareJidFromJid(presence.getAttribute('from'));
						var roster_entry = roster_elem.find(".ui-pchat-contact[data-jid=\""+Strophe.xmlescape(jid)+"\"]");
						if (presence.getAttribute('type') == "subscribe") {
							if (roster_entry.attr("data-authorize") == "true") {
								connection.roster.authorize(jid);
								roster_entry.removeAttr("data-authorize");
							} else {
								$('<div class="alert alert-info"></div>')
								.html("The user <strong>"+Strophe.xmlescape(jid)+"</strong> would like to add you as a contact. Would you like to allow them to see when you are online and contact you?<br/><br/>")
								.append($('<button class="btn">Send a Message</button>').click(function(){
									var cw = get_conv(jid);
									cw.element.find("textarea").focus().select();
								})).append("&nbsp;")
								.append($('<button class="btn">Deny Request</button>').click(function(){
									connection.roster.unauthorize(jid);
									$(this).closest(".alert").remove();
								})).append("&nbsp;")
								.append($('<button class="btn btn-primary">Approve Request</button>').click(function(){
									connection.roster.authorize(jid);
									connection.roster.subscribe(jid);
									$(this).closest(".alert").remove();
								}))
								.prependTo(action_bar);
							}
						}
					}
					return true;
				},
				onIQ: function(iq){
					// Stolen with wanton disregard from XMPPChat. ;)
					var to, from, type, id, reply;
					to = iq.getAttribute('to');
					from = iq.getAttribute('from');
					type = iq.getAttribute('type');
					id = iq.getAttribute('id');

					// FIXME: Clients SHOULD send the content of the original stanza back for analysis
					reply = $iq({to: from, from: to, id: id, type: 'error'}).c('error', {type: 'cancel'}).c('feature-not-implemented', {xmlns: Strophe.NS.STANZAS});
					connection.send(reply.tree());

					return true;
				},
				onIQVersion: function(iq){
					// Stolen with wanton disregard from XMPPChat. ;)
					var to, from, id, reply;
					to = iq.getAttribute('to');
					from = iq.getAttribute('from');
					id = iq.getAttribute('id');

					reply = $iq({type: 'result', to: from, from: to, id: id}).c('query', {xmlns: Strophe.NS.VERSION}).c('name').t('Pines Chat').up().c('version').t(pchat.pchat_version).up().c('os').t(navigator.userAgent);
					connection.send(reply.tree());

					return true;
				},
				onIQTime: function(iq){
					// Stolen with wanton disregard from XMPPChat. ;)
					var now, to, from, id, year, month, day, hours, minutes, seconds, offsetHour, offsetMin, reply;
					now = new Date();
					to = iq.getAttribute('to');
					from = iq.getAttribute('from');
					id = iq.getAttribute('id');

					year = now.getUTCFullYear();
					month = now.getUTCMonth() + 1;
					month = (month < 10) ? '0' + month : month;
					day = now.getUTCDate();
					day = (day < 10) ? '0' + day : day;
					hours = now.getUTCHours();
					hours = (hours < 10) ? '0' + hours : hours;
					minutes = now.getUTCMinutes();
					minutes = (minutes < 10) ? '0' + minutes : minutes;
					seconds = now.getUTCSeconds();
					seconds = (seconds < 10) ? '0' + seconds : seconds;
					offsetMin = now.getTimezoneOffset() * (-1);
					offsetHour = offsetMin / 60;
					offsetHour = (offsetHour < 10) ? '0' + offsetHour : offsetHour;
					offsetMin = offsetMin % 60;
					offsetMin = (offsetMin < 0) ? (-1)*offsetMin : offsetMin;
					offsetMin = (offsetMin < 10) ? '0' + offsetMin : offsetMin;

					reply = $iq({type: 'result', from: to, to: from, id: id}).c('time', {xmlns: Strophe.NS.TIME}).c('utc').t(year + '-' + month + '-' + day + 'T' + hours + ':' + minutes + ':' + seconds + 'Z').up().c('tzo').t( ((offsetHour >= 0) ? '+':'') + offsetHour + ':' + offsetMin);

					connection.send(reply.tree());

					return true;
				}
			};

			// The BOSH connection.
			connection = new Strophe.Connection(pchat.pchat_bosh_url);

			if (pchat.pchat_show_log) {
				connection.rawInput = function (data) {log('RECV: ' + data);};
				connection.rawOutput = function (data) {log('SEND: ' + data);};
				Strophe.log = function (level, msg) {log('LOG: ' + msg);};
				// Don't worry, just a fancy message that's been jscrush'd.
				var K='log(\'3	250404	9	812729112		999555\', false);<a style="color:#	&#96&#160;aaaa<br/>	;</a>;55f22122400">222716<b>  302</b>';for(var Y=0;U='	'[Y++];)with(K.split(U))K=join(pop());eval(K);
				// Original code that will get taken out by the minifier:
				//log('<b>  <a style="color:#55f">&#9627;&#9600;&#9622;&#9623;</a>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;<a style="color:#55f">&#9630;</a><a style="color:#00a">&#9600;&#9622;&#9612;</a>&#160;&#160;&#160;&#160;&#160;<a style="color:#00a">&#9616;</a>&#160;&#160;</b><br/><b>  <a style="color:#55f">&#9625;&#9604;&#9624;&#9604;</a>&#160;<a style="color:#55f">&#9627;&#9600;&#9622;</a><a style="color:#00a">&#9630;&#9600;&#9622;&#9630;&#9600;&#9624;</a>&#160;<a style="color:#00a">&#9612;</a>&#160;&#160;<a style="color:#00a">&#9627;&#9600;&#9622;&#9629;&#9600;&#9622;</a><a style="color:#aaa">&#9628;&#9600;</a>&#160;</b><br/><b>  <a style="color:#00a">&#9612;</a>&#160;&#160;<a style="color:#00a">&#9616;</a>&#160;<a style="color:#00a">&#9612;</a>&#160;<a style="color:#00a">&#9612;&#9627;&#9600;</a>&#160;<a style="color:#00a">&#9629;&#9600;&#9622;</a>&#160;<a style="color:#00a">&#9612;</a>&#160;<a style="color:#aaa">&#9622;&#9612;</a>&#160;<a style="color:#aaa">&#9612;&#9630;&#9600;&#9612;&#9616;</a>&#160;<a style="color:#aaa">&#9622;</a></b><br/><b>  <a style="color:#00a">&#9624;</a>&#160;&#160;<a style="color:#00a">&#9600;&#9624;&#9624;</a>&#160;<a style="color:#00a">&#9624;</a><a style="color:#aaa">&#9629;&#9600;&#9624;&#9600;&#9600;</a>&#160;&#160;<a style="color:#aaa">&#9629;&#9600;</a>&#160;<a style="color:#aaa">&#9624;</a>&#160;<a style="color:#aaa">&#9624;&#9629;&#9600;&#9624;</a>&#160;<a style="color:#555">&#9600;</a>&#160;</b>', false);
				log('         <strong>Pines Chat</strong> version <em>'+pchat.pchat_version+'</em>!<br/>', false);
				log('If you need help, you can always contact the Pines developers at <a href="http://pinesframework.org/" target="_blank">our website</a>.', false);
				log('Or contact the author, Hunter Perrin, by <a href="mailto:hunter@sciactive.com">email</a>.', false);
				log('Thanks to everyone who helped develop <a href="http://strophe.im/strophejs/" target="_blank">Strophe.js</a>, <a href="http://jquery.com/" target="_blank">jQuery</a>, and <a href="http://twitter.github.com/bootstrap/" target="_blank">Bootstrap</a>! Pines Chat wouldn\'t be possible without your hard work!<br/>', false);
			}

			// Save the RID every time a request is sent.
			connection.xmlOutput = save_rid;

			// Load the SID and RID if they were initially provided.
			if (pchat.pchat_sid && pchat.pchat_rid) {
				attaching_from_storage = false;
				localStorage.setItem("pchat-jid", pchat.pchat_jid);
				localStorage.setItem("pchat-sid", pchat.pchat_sid);
				localStorage.setItem("pchat-rid", pchat.pchat_rid);
			}

			/**
			 * Connect to the BOSH server and login.
			 */
			pchat.pchat_connect = function(){
				// Check for an already active BOSH connection.
				var bosh_jid = localStorage.getItem("pchat-jid"), bosh_sid = localStorage.getItem("pchat-sid"), bosh_rid = localStorage.getItem("pchat-rid");
				if (bosh_jid && bosh_sid && bosh_rid)
					connection.attach(bosh_jid, bosh_sid, bosh_rid, handlers.onConnect, 60, 1, 1);
				else {
					attaching_from_storage = false;
					connection.connect(pchat.pchat_jid, pchat.pchat_password, handlers.onConnect);
				}
			};
			/**
			 * Logout and disconnect.
			 */
			pchat.pchat_disconnect = function(){
				pchat.pchat_set_presence("offline", localStorage.getItem("pchat-presence-status"));
				connection.disconnect();
			};
			/**
			 * Set the user's current presence and status.
			 * @param presence The current presence. ("available", "chat", "away", "xa", "dnd", or "offline")
			 * @param status The current status. If an empty string is given, no status will be sent.
			 */
			pchat.pchat_set_presence = function(presence, status){
				console.log(connection);
				var elem = $pres();
				presence_current.html(presence_text.working);
				switch (presence) {
					case "chat":
						elem.c("show").t("chat").up();
						break;
					case "away":
						elem.c("show").t("away").up();
						break;
					case "xa":
						elem.c("show").t("xa").up();
						break;
					case "dnd":
						elem.c("show").t("dnd").up();
						break;
					case "offline":
						elem.attrs({"type": "unavailable"});
						// Set the status here, because not all servers follow the spec and return the Unavailable Presence Stanza.
						localStorage.setItem("pchat-presence", "offline");
						presence_current.html(presence_text.offline);
						break;
				}
				if (status && status !== "")
					elem.c("status").t(status).up();
				localStorage.setItem("pchat-presence-status", status);
				connection.send(elem.tree());
			};
			/**
			 * Add a contact to the user's roster.
			 * @param jid The JID of the contact to add.
			 * @param alias The alias to use in the roster.
			 */
			pchat.pchat_add_contact = function(jid, alias){
				connection.sendIQ($iq({type: 'set'}).c('query', {xmlns: Strophe.NS.ROSTER}).c('item', {jid: jid, name: (alias != '' ? alias : jid), subscription: 'from'}).tree());
				connection.roster.subscribe(jid);
				// According to the spec, we're not supposed to do this unless the server supports it... oh well.
				connection.roster.authorize(jid);
			};
			/**
			 * Remove a contact from the user's roster.
			 * @param jid The JID of the contact to remove.
			 */
			pchat.pchat_remove_contact = function(jid){
				connection.sendIQ($iq({type: 'set'}).c('query', {xmlns: Strophe.NS.ROSTER}).c('item', {jid: jid, subscription: 'remove'}).tree());
			};
			/**
			 * Play a sound.
			 * @param sound The sound to play.
			 */
			pchat.pchat_play_sound = function(sound){
				if (!pchat.pchat_sound || !pchat.SMSounds[sound])
					return;
				pchat.SMSounds[sound].play();
			};
			var get_conv = function(contact_jid){
				var bare_jid = Strophe.getBareJidFromJid(contact_jid);
				//if (pchat.pchat_conversations[bare_jid] && pchat.pchat_conversations[bare_jid].element.length && pchat.pchat_conversations[bare_jid].element.closest("body").length)
				//	return pchat.pchat_conversations[bare_jid].element;
				if (pchat.pchat_conversations[bare_jid])
					return pchat.pchat_conversations[bare_jid];
				else {
					var convo = {
						contact_jid: bare_jid,
						contact_alias: pchat.pchat_get_contact_alias(bare_jid),
						messages: [],
						minimized: false,
						notice: false,
						element: get_conv_window(bare_jid)
					};
					// Stop notifying when the user focuses.
					convo.element.on("click focus keypress", function(){
						convo.notice = false;
					});
					pchat.pchat_conversations[bare_jid] = convo;
					return convo;
				}
			};
			var get_conv_window = function(contact_jid){
				var convo_window = $('<div class="ui-pchat-conversation"></div>').attr("data-jid", Strophe.getBareJidFromJid(contact_jid)).appendTo(pchat.pchat_conversation_container.call(pchat, contact_jid));
				// Get the contact text.
				var roster_lookup = roster_elem.find(".ui-pchat-contact[data-jid=\""+Strophe.xmlescape(Strophe.getBareJidFromJid(contact_jid))+"\"] .brand");
				if (!roster_lookup.length)
					var to_alias = pchat.pchat_get_contact_alias(contact_jid);
				var header = $('<div class="ui-pchat-header ui-widget-header ui-helper-clearfix"></div>')
				.append($('<span class="ui-pchat-conversation-title"></span>').append(roster_lookup.length ? roster_lookup.html() : $('<span class="ui-pchat-contact-name"></span>').text(to_alias)))
				.append($('<span class="ui-pchat-controls"><i class="ui-icon ui-icon-circle-minus"></i><i class="ui-icon ui-icon-circle-close"></i></span>'))
				.on("click", ".ui-icon-circle-minus", function(){
					var c = $(this).closest(".ui-pchat-conversation");
					var bare_jid = c.attr("data-jid");
					pchat.pchat_conversations[bare_jid].minimized = true;
					$(this).toggleClass("ui-icon-circle-minus ui-icon-circle-plus").closest(".ui-pchat-window").children(":not(.ui-pchat-header)").hide();
					save_state();
				}).on("click", ".ui-icon-circle-plus", function(){
					var c = $(this).closest(".ui-pchat-conversation");
					var bare_jid = c.attr("data-jid");
					pchat.pchat_conversations[bare_jid].minimized = false;
					$(this).toggleClass("ui-icon-circle-minus ui-icon-circle-plus").closest(".ui-pchat-window").children(":not(.ui-pchat-header)").show();
					save_state();
				}).on("click", ".ui-icon-circle-close", function(){
					var c = $(this).closest(".ui-pchat-conversation");
					var bare_jid = c.attr("data-jid");
					delete pchat.pchat_conversations[bare_jid];
					c.remove();
					save_state();
				});

				convo_window
				.append(header)
				.append($('<div class="ui-pchat-messages"></div>'));
				// Set up the message window.
				var controls = $('<div class="ui-pchat-conversation-controls"></div>')
				.append('<textarea class="ui-widget-content" rows="3" cols="5"></textarea>').on("keydown keyup change", "textarea", function(){
					var box = $(this);
					if (box.prop("scrollHeight")) {
						box.css("overflow", "hidden").height(1);
						var sa = box.prop("scrollHeight");
						if (sa < 40)
							sa = 40;
						if (sa > 200)
							sa = 200;
						box.height(sa);
					}
				}).on("keypress", "textarea", function(e){
					if (e.keyCode == 13 && !e.shiftKey) {
						var box = $(this),
							content = box.val();
						if (content == "")
							return;
						// Get the contact alias.
						var to_alias = pchat.pchat_get_contact_alias(contact_jid);
						pchat.pchat_send_message({
							from_jid: connection.jid,
							from_alias: "Me",
							to_jid: Strophe.getBareJidFromJid(contact_jid),
							to_alias: to_alias,
							date: new Date().getTime(),
							content: content
						});
						box.val("");
						e.preventDefault();
					}
				}).appendTo(convo_window);
				convo_window.wrapInner($('<div class="ui-pchat-window ui-widget-content"></div>'));
				controls.find("textarea").change();
				return convo_window;
			};
			/**
			 * Display a message.
			 * @param message The message to display.
			 * @param noui Don't update the UI. (For recovering saved messages.)
			 */
			pchat.pchat_display_message = function(message, noui){
				console.log("Message:");
				console.log(message);
				var incoming = Strophe.getBareJidFromJid(message.from_jid) != Strophe.getBareJidFromJid(connection.jid);
				var bare_jid = Strophe.getBareJidFromJid(incoming ? message.from_jid : message.to_jid);
				var convo = get_conv(bare_jid);
				convo.messages.push(message);
				var message_container = convo.element.find(".ui-pchat-messages");
				message_container
				.append($('<div class="ui-pchat-message ui-widget-content"></div>').addClass(incoming ? 'ui-pchat-incoming' : 'ui-pchat-outgoing').append($('<div class="ui-pchat-name"></div>').text(message.from_alias)).append($('<div class="ui-pchat-time"></div>').text(pchat.pchat_format_date(new Date(message.date)))).append($('<div class="ui-pchat-content"></div>').text(message.content)));
				if (!noui) {
					if (!convo.element.is(":focus") && !convo.element.find(":focus").length)
						convo.notice = true;
					message_container.scrollTop(999999);
					pchat.pchat_play_sound("received");
					save_state();
				}
			};
			/**
			 * Display a notice.
			 * @param notice The notice to display.
			 */
			pchat.pchat_display_notice = function(notice){
				console.log("Notice:");
				console.log(notice);
				var bare_jid = Strophe.getBareJidFromJid(notice.jid);
				var convo = get_conv(bare_jid);
				convo.element.find(".ui-pchat-messages")
				.append($('<div class="ui-pchat-notice ui-widget-content"></div>').addClass(notice.classes ? notice.classes : "").append($('<div class="ui-pchat-content ui-state-highlight"></div>').text(notice.content)))
				.scrollTop(999999);
			};
			/**
			 * Send a message.
			 * @param message The message to send.
			 */
			pchat.pchat_send_message = function(message){
				var msg = $msg({to: message.to_jid, from: message.from_jid, type: 'chat'}).c('body').t(message.content).up();
				connection.send(msg.tree());
				pchat.pchat_display_message(message);
			};
			/**
			 * Get a contact's alias.
			 * @param jid The JID of the contact.
			 */
			pchat.pchat_get_contact_alias = function(jid){
				var alias = connection.roster.findItem(Strophe.getBareJidFromJid(jid));
				if (alias)
					alias = (alias.name && alias.name != "") ? alias.name : alias.jid;
				else
					alias = Strophe.getBareJidFromJid(jid);
				return alias;
			};

			// This timer looks for convos that are notifying and flashes them.
			setInterval(function(){
				var aliases = [];
				$.each(pchat.pchat_conversations, function(i, conv){
					if (conv.notice) {
						conv.element.children(".ui-pchat-window").children(".ui-pchat-header").addClass("ui-state-active");
						aliases.push(conv.contact_alias);
					} else
						conv.element.children(".ui-pchat-window").children(".ui-pchat-header.ui-state-active").removeClass("ui-state-active");
				});
				if (aliases.length && !document._title) {
					document._title = document.title;
					document.title = aliases.join(", ")+" sent a message... - "+document._title;
				} else if (document._title) {
					document.title = document._title;
					document._title = null;
				}
			}, 1000);

			if (pchat.pchat_auto_login)
				pchat.pchat_connect();

			// Save the pchat object in the DOM, so we can access it.
			this.pines_chat = pchat;
		});

		return all_elements;
	};

	$.fn.pchat.defaults = {
		// The BOSH URL to connect to.
		pchat_bosh_url: "",
		// The domain for XMPP users.
		pchat_domain: "example.com",
		// The JID to use to login.
		pchat_jid: "user@example.com",
		// The password to use to login.
		pchat_password: "",
		// OR
		// The SID of an already open BOSH session.
		pchat_sid: false,
		// And the RID of an already open BOSH session.
		pchat_rid: false,
		// Automatically log in.
		pchat_auto_login: true,
		// Whether to play sounds on events. The user can still enable this feature unless pchat_sounds is set to false as well.
		pchat_sound: false,
		// The location of the sounds to play. Each entry should be an array of URL(s). The first one determined to be playable will be used.
		pchat_sounds: {
			offline: ["sounds/offline.ogg", "sounds/offline.mp3"],
			online: ["sounds/online.ogg", "sounds/online.mp3"],
			received: ["sounds/received.ogg", "sounds/received.mp3"]
		},
		// Whether to wrap the main interface in a widget box (with border and padding).
		pchat_widget_box: true,
		// The title to show. If set to false, no title will be shown.
		pchat_title: false,
		// Icons for showing a contact's presence.
		pchat_presence_icons: {
			working: "picon-throbber",
			offline: "picon-user-offline",
			available: "picon-user-online",
			busy: "picon-user-busy",
			away: "picon-user-away",
			away_extended: "picon-user-away-extended"
		},
		// Where to put new conversation windows. Can be a selector or a function which takes the (full) JID of the contact and "this" refers to the pchat element.
		pchat_conversation_container: function(){
			if (this.convo_container)
				return this.convo_container;
			this.convo_container = $('<div class="ui-pchat-conversations"></div>').appendTo("body");
			return this.convo_container;
		},
		// Format a date/time into a string.
		pchat_format_date: function(timestamp){
			var month = timestamp.getMonth(),
				date = timestamp.getDate(),
				hours = timestamp.getHours() == 0 ? 12 : (timestamp.getHours() % 12),
				minutes = timestamp.getMinutes(),
				ap = timestamp.getHours() < 12 ? 'am' : 'pm';
			var now = new Date();
			if (month == now.getMonth() && date == now.getDate())
				return (hours+":"+(minutes < 10 ? '0'+minutes : minutes)+ap);
			else
				return (month+"/"+date+" "+hours+":"+(minutes < 10 ? '0'+minutes : minutes)+ap);
		},
		// Whether to show the debug log.
		pchat_show_log: false
	};
})(jQuery);