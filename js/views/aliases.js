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
		initialize: function(options) {
			this.collection = new Backbone.Collection(options.collection);
			this.listenTo(Radio.aliases, 'aliases:change', this.resetAlias);
		},
		resetAlias: function(aliases) {
			this.collection.reset(aliases);
		}
	});
});
