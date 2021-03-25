
// import { Sortable, Plugins } from '@shopify/draggable'

const Data = {
	ignore			: 'data-draggable-ignore',
	widgetAdd		: 'data-widget-add',
	widgetRemove	: 'data-widget-remove',
	widgetList		: 'data-widget-list'
}

const Classes = {
	dropzone		: 'sortable',
	draggable		: 'sortitem'
}

const $body			= $('body')

const dragDelay		= 0
const saveDelay		= 0


let to = false


const sortable = new Draggable.Sortable($(`.${Classes.dropzone}`).get(), {
	draggable: `.${Classes.draggable}`,
	mirror: {
		constrainDimensions: true,
		cursorOffsetX: 0,
		cursorOffsetY: 0
	},
	plugins: [Draggable.Plugins.ResizeMirror],
	delay: dragDelay
})

/* events */

sortable.on('drag:start', (evt) => {
	let $target = $(evt.data.sensorEvent.data.target)

	clearTimeout(to)

	if($target.parents(`[${Data.ignore}], a`).length > 0 || $target.is(`[${Data.ignore}], a`)) {
		sortable.dragging = false
		evt.cancel()
	} else {
		$body.addClass('draggable--show-grid')
	}
})

sortable.on('sortable:stop', (evt) => {
	to = setTimeout(() => {
		$body.removeClass('draggable--show-grid')
        ioCall('setWidgetPosition', [
            $(evt.data.dragEvent.data.originalSource).attr('ref'),
            $(evt.data.newContainer).prop('id'),
            evt.data.newIndex
        ]);
    }, saveDelay)
})

window.a = sortable

$(document).on('click', `[${Data.widgetAdd}]`, (e) => {
	e.preventDefault()
	// widget zum dashboard hinzufügen
})

$(document).on('click', `[${Data.widgetRemove}]`, (e) => {
	e.preventDefault()
	// widget vom dashboard entfernen und zur liste hinzufügen
})

$(function () {
    $('.widget').each(function (i, widget) {
        var widgetId = $(widget).attr('ref');
        var $widgetContent = $('.widget-content', widget);
        var $widget = $(widget);
        var hidden = $('.widget-hidden', widget).length > 0;

        // add click handler for widgets collapse button
        $('<a href="#" class="btn-sm"><i class="fa fa-chevron-' + (hidden ? 'down' : 'up') + '"></li></a>')
            .on('click', function (e) {
                if ($widgetContent.is(':hidden')) {
                    ioCall('expandWidget', [widgetId, 1], undefined, undefined, undefined, true);
                    $widgetContent.slideDown('fast');
                    $('i', this).attr('class', 'fa fa-chevron-up');
                } else {
                    ioCall('expandWidget', [widgetId, 0], undefined, undefined, undefined, true);
                    $widgetContent.slideUp('fast');
                    $('i', this).attr('class', 'fa fa-chevron-down');
                }
                e.preventDefault();
            })
            .appendTo($('.options', widget));

        // add click handler for widgets close button
        $('<a href="#" class="ml-2 btn-sm"><i class="fal fa-times"></li></a>')
            .on('click', function (e) {
                e.preventDefault();
                ioCall('closeWidget', [widgetId], function (result) {
                    ioCall('getAvailableWidgets');
                    $widget.slideUp('fast');
                });
            })
            .appendTo($('.options', widget));
    });
})
