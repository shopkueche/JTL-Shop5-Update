
// import Chart from 'chart.js'

/* plugins */

const lineChartAreaGradients = {
	id: 'areagradients',

	afterLayout: function(chart, options) {
		const scales = chart.scales;
		const hexToRgb = hex => hex.replace(/^#?([a-f\d])([a-f\d])([a-f\d])$/i, (m, r, g, b) => '#' + r + r + g + g + b + b).substring(1).match(/.{2}/g).map(x => parseInt(x, 16))

		chart.data.datasets.forEach((dataset, i) => {
			let color = hexToRgb(dataset.borderColor)
			let gradient = chart.ctx.createLinearGradient(0, scales["y-axis-0"].top, 0, scales["y-axis-0"].bottom)

			gradient.addColorStop(0, `rgba(${color[0]}, ${color[1]}, ${color[2]}, 0.6`);
			gradient.addColorStop(1, `rgba(${color[0]}, ${color[1]}, ${color[2]}, 0`);

			chart.data.datasets[i].backgroundColor = gradient;
		})
	}
}

Chart.plugins.register(lineChartAreaGradients)

/* options */

let options = {
	scales: {
		xAxes: [{
			gridLines: {
				zeroLineColor: 'rgba(0, 0, 0, 0)',
				color: '#eaf0f4',
				lineWidth: 1,
				tickMarkLength: 0,
			},
			ticks: {
				padding: 10,
				fontColor: '#5cbcf6',
				fontSize: 12,
				stepSize: 5
			}
		}],
		yAxes: [{
			gridLines: {
				zeroLineColor: 'rgba(0, 0, 0, 0)',
				color: '#eaf0f4',
				lineWidth: 1,
				tickMarkLength: 0,
			},
			ticks: {
				padding: 15,
				fontColor: '#5cbcf6',
				fontSize: 12,
				stepSize: 50
			}
		}]
	},
	elements: {
		line: {
			tension: 0
		},
		point: {
			radius: 6,
		},
	}
}

Chart.defaults.global = $.extend(true, Chart.defaults.global, options)

/* events */

document.dispatchEvent(new CustomEvent('charts-init', {
	detail: {
		chart : Chart
	}
}))
