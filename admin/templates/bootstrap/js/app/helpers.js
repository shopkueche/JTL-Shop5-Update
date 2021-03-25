
export const debounce = (fn, wait = 100) => {
	let timeout
	return (...args) => {
		clearTimeout(timeout)
		timeout = setTimeout(() => fn(...args), wait)
	}
}

export const throttle = (fn, wait = 100) => {
	let timeout
	return (...args) => {
		if(timeout)
			return

		fn(...args)
		timeout = true
		setTimeout(() => timeout = false, wait)
	}
}
