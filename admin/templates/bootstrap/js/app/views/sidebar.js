
// import Store from 'store'

const Classes = {
	collapseState: 'sidebar-collapsed'
}

const $sidebar = $('#sidebar')

const storeKeySidebarState = 'jtlshop-sidebar-state'

export const setView = (state = false) => {
	$sidebar[state ? 'addClass' : 'removeClass'](Classes.collapseState)
	store.set(storeKeySidebarState, state)
}

setView(store.get(storeKeySidebarState))

/* events */

$(document).on('click', '[data-toggle="sidebar-collapse"]', () => {
	setView(!$sidebar.hasClass(Classes.collapseState))
})
