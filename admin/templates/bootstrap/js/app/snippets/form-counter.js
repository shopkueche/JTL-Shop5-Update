
// import $ from 'jquery'

const Classes = {
	counter	: 'form-counter'
}

const Data = {
	up		: 'data-count-up',
	down	: 'data-count-down'
}

const updateCount = (counter, value) => {
	counter.get(0)[`step${value.capitalize()}`]()
}

$(`[${Data.down}]`).on('click', (event) => {
	updateCount($(event.currentTarget).parents(`.${Classes.counter}`).find('input'), 'down')
})

$(`[${Data.up}]`).on('click', (event) => {
	updateCount($(event.currentTarget).parents(`.${Classes.counter}`).find('input'), 'up')
})

$(`.${Classes.counter} input[type='number']`).on('keyup blur', (event) => {
	let $input = $(event.currentTarget)
	let min = parseInt($input.attr('min'))
	let max = parseInt($input.attr('max'))

	if($input.val() > max) $input.val(max)
	if($input.val() < min) $input.val(min)
})
