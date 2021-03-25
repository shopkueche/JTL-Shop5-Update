
if(typeof window.CustomEvent !== "function") {
	const CustomEvent = (event, params) => {
		params = params || { bubbles: false, cancelable: false, detail: undefined }
		return document.createEvent('CustomEvent').initCustomEvent(event, params.bubbles, params.cancelable, params.detail)
	}

	CustomEvent.prototype = window.Event.prototype

	window.CustomEvent = CustomEvent
}

String.prototype.capitalize = function() {
	return this.charAt(0).toUpperCase() + this.slice(1).toLowerCase()
}