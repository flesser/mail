/**
 * ownCloud - Mail
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Tahaa Karim <tahaalibra@gmail.com>
 * @copyright Tahaa Karim 2016
 */

define(function(require) {
	'use strict';

	var $ = require('jquery');
	var Marionette = require('marionette');
	var Handlebars = require('handlebars');
	var AliasesListTemplate = require('text!templates/aliases-list.html');
	var Radio = require('radio');

	return Marionette.CompositeView.extend({
		collection: null,
		model: null,
		childViewContainer: 'tbody',
		template: Handlebars.compile(AliasesListTemplate),
		templateHelpers: function() {
			return {
				aliases: this.model.toJSON()
			};
		},
		ui: {
			'deleteButton' : 'button'
		},
		events: {
			'click @ui.deleteButton': 'deleteAlias'
		},
		initialize: function(options) {
			this.model = options.model;
		},
		deleteAlias: function() {
			event.stopPropagation();
			var account = require('state').accounts.get(this.model.get('accountId'));
			var aliasId = this.model.get('id');
			var deletingAlias = Radio.aliases.trigger('delete', account, aliasId);
			$.when(deletingAlias).done(function() {
				var aliases = [];
				var json = account.toJSON();
				for (var x in json.aliases) {
					if (json.aliases[x].id !== aliasId) {
						aliases.push(json.aliases[x]);
					}
				}
				require('state').accounts.get(account.get('accountId')).set({'aliases': aliases});
			});
		}

	});
});
