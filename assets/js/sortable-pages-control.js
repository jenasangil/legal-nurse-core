(function( $ ) {
    var SortablePagesControlView = elementor.modules.controls.BaseData.extend({

        onReady: function() {
            var self = this;
            var value = this.getControlValue() || []; // array of {id, title}

            this.renderList( value );

            this.ui.select = this.$el.find( '.sortable-pages-select' );
            this.ui.list   = this.$el.find( '.sortable-pages-list' );

            this.ui.select.select2({
                dropdownParent: this.$el,
            });

            this.ui.select.on( 'change', function() {
                var id = $( this ).val();
                if ( ! id ) return;

                var title = $( this ).find( 'option:selected' ).text();
                var current = self.getControlValue() || [];

                // prevent duplicates
                if ( _.findWhere( current, { id: id } ) ) {
                    $( this ).val( '' ).trigger( 'change.select2' );
                    return;
                }

                current.push( { id: id, title: title } );
                self.setValue( current );
                self.renderList( current );

                $( this ).val( '' ).trigger( 'change.select2' );
            });
        },

        renderList: function( items ) {
            var self = this;
            this.ui.list = this.$el.find( '.sortable-pages-list' );
            this.ui.list.empty();

            _.each( items, function( item ) {
                self.ui.list.append(
                    '<li class="sortable-page-item" data-id="' + item.id + '" ' +
                    'style="padding:6px 10px;margin-bottom:4px;background:#f4f4f4;border:1px solid #ddd;cursor:move;display:flex;justify-content:space-between;align-items:center;border-radius:3px;">' +
                        '<span>' + item.title + '</span>' +
                        '<a href="#" class="remove-page" style="color:#c00;text-decoration:none;font-weight:bold;">&times;</a>' +
                    '</li>'
                );
            });

            this.ui.list.sortable({
                axis: 'y',
                update: function() {
                    var newOrder = [];
                    self.ui.list.find( '.sortable-page-item' ).each( function() {
                        var id = $( this ).data( 'id' );
                        var found = _.findWhere( self.getControlValue(), { id: String( id ) } );
                        if ( found ) newOrder.push( found );
                    });
                    self.setValue( newOrder );
                }
            });

            this.ui.list.off( 'click', '.remove-page' ).on( 'click', '.remove-page', function( e ) {
                e.preventDefault();
                var id = $( this ).closest( '.sortable-page-item' ).data( 'id' );
                var current = _.reject( self.getControlValue(), function( item ) {
                    return item.id === String( id );
                });
                self.setValue( current );
                self.renderList( current );
            });
        }

    });

    elementor.addControlView( 'sortable_pages', SortablePagesControlView );

})( jQuery );
