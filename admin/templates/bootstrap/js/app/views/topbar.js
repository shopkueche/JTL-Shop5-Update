
const topbar		   = '#topbar'
const search		   = `${topbar} .search input`
const searchIcon	   = `${topbar} .search-icon`
const searchBackground = `${topbar} .opaque-background`
const dropdown		   = '#dropdown-search'

const $topbar		= $(topbar)
const $search		= $(search)
const $dropdown		= $(dropdown)

const minTyped		= 3


const checkSearchDropdown = () => {
	let typed = $search.val()
	let show = typed.length >= minTyped

	$dropdown.dropdown(show ? 'show' : 'hide')
}

/* events */

$(document).on('focus', search, () => {
	$topbar.addClass('searching')
	checkSearchDropdown()
})

$(document).on('keyup', search, () => {
	checkSearchDropdown()
})

$(document).on('click', searchIcon, () => {
	$search.focus()
})

$(document).on('click', (e) => {
	let $target = $(e.target)
	let isTopbar = ($target.parents(topbar).length > 0 || $target.is(topbar)) && !$target.is(searchBackground)

	if(isTopbar)
		return

	$topbar.removeClass('searching')
	$dropdown.dropdown('hide')
})