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

	var Marionette = require('marionette');
	var AliasesListView = require('views/aliases-list');
	var Backbone = require('backbone');
	var Radio = require('radio');

	return Marionette.CollectionView.extend({
		collection: null,
		childView: AliasesListView,
		currentAccount: null,
		initialize: function(options) {
			this.currentAccount = options.currentAccount;
			this.collection = new Backbone.Collection(this.currentAccount.get('aliases'));
			this.listenTo(Radio.aliases, 'alias:add', this.addAlias);
			this.listenTo(Radio.aliases, 'alias:remove', this.removeAlias);
		},
		addAlias: function(alias) {
			this.collection.add(alias);
		},
		removeAlias: function(alias) {
			this.collection.remove(alias);
		}
	});
});
