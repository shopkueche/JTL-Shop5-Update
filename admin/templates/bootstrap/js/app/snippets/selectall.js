
import { debounce } from '../helpers.js'

const Data = {
	select			: 'data-select-all',
	deselect		: 'data-deselect-all',
	count			: 'data-select-all-count'
}

const updateCount = () => {
	let $counter = $(`[${Data.count}]`)

	if($counter.length == 0)
		return

	$.each($counter, (i, element) => {
		let $element = $(element)
		let id = $element.attr(`${Data.count}`)

		$element.html($(`#${id}`).find('[type="checkbox"]:checked').length)
	})
}

updateCount()

/* events */

$(document).on('click', `[${Data.select}]`, function() {
	let id = $(this).attr(`${Data.select}`)
	$(`#${id}`).find('[type="checkbox"]').prop('checked', true).trigger('change')
})

$(document).on('click', `[${Data.deselect}]`, function() {
	let id = $(this).attr(`${Data.deselect}`)
	$(`#${id}`).find('[type="checkbox"]').prop('checked', false).trigger('change')
})

$(document).on('change', `[type="checkbox"]`, debounce(updateCount))
