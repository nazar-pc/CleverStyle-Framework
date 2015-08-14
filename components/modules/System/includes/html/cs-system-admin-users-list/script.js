// Generated by CoffeeScript 1.9.3

/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */

(function() {
  var GUEST_ID, L, ROOT_ID, STATUS_ACTIVE, STATUS_INACTIVE;

  L = cs.Language;

  STATUS_ACTIVE = 1;

  STATUS_INACTIVE = 0;

  GUEST_ID = 1;

  ROOT_ID = 2;

  Polymer({
    'is': 'cs-system-admin-users-list',
    behaviors: [cs.Polymer.behaviors.Language],
    properties: {
      tooltip_animation: '{animation:true,delay:200}',
      search_column: '',
      search_mode: 'LIKE',
      search_text: {
        observer: 'search_textChanged',
        type: String,
        value: ''
      },
      search_page: {
        observer: 'search',
        type: Number,
        value: 1
      },
      search_limit: 20,
      search_columns: [],
      search_modes: [],
      all_columns: [],
      columns: ['id', 'login', 'username', 'email'],
      users: [],
      users_count: 0,
      show_pagination: {
        type: Boolean,
        computed: 'show_pagination_(users_count, search_limit, search_page)'
      },
      searching: false,
      searching_loader: false
    },
    observers: ['search_again(search_column, search_mode, search_limit)'],
    ready: function() {
      $.ajax({
        url: 'api/System/admin/users',
        type: 'search_options',
        success: (function(_this) {
          return function(search_options) {
            var column, i, len, ref, search_columns;
            search_columns = [];
            ref = search_options.columns;
            for (i = 0, len = ref.length; i < len; i++) {
              column = ref[i];
              search_columns.push({
                name: column,
                selected: _this.columns.indexOf(column) !== -1
              });
            }
            _this.search_columns = search_columns;
            _this.all_columns = search_options.columns;
            _this.search_modes = search_options.modes;
            return _this.search();
          };
        })(this)
      });
      $(this.$['pagination-top'], this.$['pagination-bottom']).on('select.uk.pagination', (function(_this) {
        return function(e, pageIndex) {
          return _this.search_page = pageIndex + 1;
        };
      })(this));
      this.workarounds(this.shadowRoot);
      return cs.observe_inserts_on(this.shadowRoot, this.workarounds);
    },
    workarounds: function(target) {
      return $(target).cs().pagination_inside().cs().tabs_inside().cs().tooltips_inside();
    },
    search: function() {
      var searching_timeout;
      if (!this.search_modes || this.searching) {
        return;
      }
      this.searching = true;
      searching_timeout = setTimeout(((function(_this) {
        return function() {
          return _this.searching_loader = true;
        };
      })(this)), 200);
      return $.ajax({
        url: 'api/System/admin/users',
        type: 'search',
        data: {
          column: this.search_column,
          mode: this.search_mode,
          text: this.search_text,
          page: this.search_page,
          limit: this.search_limit
        },
        complete: (function(_this) {
          return function(jqXHR, textStatus) {
            clearTimeout(searching_timeout);
            _this.searching = false;
            _this.searching_loader = false;
            if (!textStatus) {
              _this.set('users', []);
              return _this.users_count = 0;
            }
          };
        })(this),
        success: (function(_this) {
          return function(data) {
            _this.users_count = data.count;
            if (!data.count) {
              _this.set('users', []);
              return;
            }
            data.users.forEach(function(user) {
              var column;
              user["class"] = (function() {
                switch (parseInt(user.status)) {
                  case STATUS_ACTIVE:
                    return 'uk-alert-success';
                  case STATUS_INACTIVE:
                    return 'uk-alert-warning';
                  default:
                    return '';
                }
              })();
              user.is_guest = user.id == GUEST_ID;
              user.is_root = user.id == ROOT_ID;
              user.columns = (function() {
                var i, len, ref, results;
                ref = this.columns;
                results = [];
                for (i = 0, len = ref.length; i < len; i++) {
                  column = ref[i];
                  results.push((function(value) {
                    if (value instanceof Array) {
                      return value.join(', ');
                    } else {
                      return value;
                    }
                  })(user[column]));
                }
                return results;
              }).call(_this);
              return (function() {
                var type;
                type = user.is_root || user.is_admin ? 'a' : user.is_user ? 'u' : user.is_bot ? 'b' : 'g';
                user.type = L[type];
                return user.type_info = L[type + '_info'];
              })();
            });
            return _this.set('users', data.users);
          };
        })(this)
      });
    },
    toggle_search_column: function(e) {
      var column, index;
      index = $(e.currentTarget).data('column-index');
      column = this.search_columns[index];
      this.set(['search_columns', index, 'selected'], !column.selected);
      this.set('columns', (function() {
        var i, len, ref, results;
        ref = this.search_columns;
        results = [];
        for (i = 0, len = ref.length; i < len; i++) {
          column = ref[i];
          if (column.selected) {
            results.push(column.name);
          }
        }
        return results;
      }).call(this));
      return this.search_again();
    },
    search_again: function() {
      if (this.search_page > 1) {
        return this.search_page = 1;
      } else {
        return this.search();
      }
    },
    search_textChanged: function() {
      clearTimeout(this.search_text_timeout);
      return this.search_text_timeout = setTimeout(this.search_again.bind(this), 300);
    },
    show_pagination_: function(users_count, search_limit, search_page) {
      [UIkit.pagination(this.$['pagination-top']), UIkit.pagination(this.$['pagination-bottom'])].forEach((function(_this) {
        return function(p) {
          p.pages = Math.ceil(users_count / search_limit);
          p.currentPage = search_page - 1;
          return p.render();
        };
      })(this));
      return parseInt(users_count) > parseInt(search_limit);
    },
    add_user: function() {
      return $.cs.simple_modal("<h3>" + L.adding_a_user + "</h3>\n<cs-system-admin-users-add-user-form/>").on('hide.uk.modal', this.search.bind(this));
    },
    add_bot: function() {
      return $.cs.simple_modal("<h3>" + L.adding_a_bot + "</h3>\n<cs-system-admin-users-add-bot-form/>").on('hide.uk.modal', this.search.bind(this));
    },
    edit_user: function(e) {
      var $sender, index, title, user;
      $sender = $(e.currentTarget);
      index = $sender.closest('[data-user-index]').data('user-index');
      user = this.users[index];
      if (user.is_bot) {
        title = L.editing_of_bot_information(user.username || user.login);
        return $.cs.simple_modal("<h2>" + title + "</h2>\n<cs-system-admin-users-edit-bot-form user_id=\"" + user.id + "\"/>").on('hide.uk.modal', this.search.bind(this));
      } else {
        title = L.editing_of_user_information(user.username || user.login);
        return $.cs.simple_modal("<h2>" + title + "</h2>\n<cs-system-admin-users-edit-user-form user_id=\"" + user.id + "\"/>").on('hide.uk.modal', this.search.bind(this));
      }
    },
    edit_permissions: function(e) {
      var $sender, index, title, title_key, user;
      $sender = $(e.currentTarget);
      index = $sender.closest('[data-user-index]').data('user-index');
      user = this.users[index];
      title_key = user.is_bot ? 'permissions_for_bot' : 'permissions_for_user';
      title = L[title_key](user.username || user.login);
      return $.cs.simple_modal("<h2>" + title + "</h2>\n<cs-system-admin-permissions-for user=\"" + user.id + "\" for=\"user\"/>");
    }
  });

}).call(this);
