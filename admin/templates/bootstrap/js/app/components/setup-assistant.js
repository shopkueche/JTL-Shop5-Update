
const modal					= '#modal-setup-assistant'
const dataPrefix			= 'data-setup'

const Data = {
    current					: `${dataPrefix}-current`,
    step					: `${dataPrefix}-step`,
    slides					: `${dataPrefix}-slide`,
    prev					: `${dataPrefix}-prev`,
    next					: `${dataPrefix}-next`,
    submit					: `${dataPrefix}-submit`,
    auth					: `${dataPrefix}-auth`,
    legalToggler			: `${dataPrefix}-legal-toggle`,
    summaryPlaceholder		: `${dataPrefix}-summary-placeholder`,
    summaryId				: `${dataPrefix}-summary-id`,
    summaryText				: `${dataPrefix}-summary-text`,
    summaryPluginNote		: `#summary-plugin-note`
}

const $modal				= $(modal)
const $form					= $(`${modal} form`)
const $step					= $(`${modal} [${Data.step}]`)
const $slides				= $(`${modal} [${Data.slides}]`)
const $prev					= $(`${modal} [${Data.prev}]`)
const $next					= $(`${modal} [${Data.next}]`)
const $submit				= $(`${modal} [${Data.submit}]`)
const $auth					= $(`${modal} [${Data.auth}]`)
const $summaryPlaceholder	= $(`${modal} [${Data.summaryPlaceholder}]`)
const $summaryId			= $(`${modal} [${Data.summaryId}]`)
const $summaryText			= $(`${modal} [${Data.summaryText}]`)
const $summaryPluginNote	= $(`${modal} ${Data.summaryPluginNote}`)

const last					= $slides.length - 1


let current					= 0
let subsequent				= false

let legalPluginCount		= 0;
let paymentPluginCount		= 0;

let hasAuth					= ($('#has-auth').val() === 'true');
let authRedirect			= $('#auth-redirect').val();

let $currentSlide			= $(`${modal} [${Data.slides}='${current}']`)

let criticalErrors			= true;


const showSlide = slide => {
    if (slide === current && subsequent) {
        return
    }
    current = slide

    $modal.attr(Data.current, current)

    $currentSlide = $(`${modal} [${Data.slides}='${slide}']`)

    $step.removeClass('active')
    $step.filter(function() {
        return $(this).is(`[${Data.step}="${slide}"]`)
    }).addClass('active')

    $slides.removeClass('active')
    $currentSlide.addClass('active')
}

const updateSummary = (slide = current) => {
    let summaries = {}
    let $summaries = $currentSlide.find(`[${Data.summaryId}]`)
    criticalErrors= true;

    $.each($summaries, (index, summary) => {
        let $summary = $(summary)
        let isCheckbox = $summary.is(':checkbox')
        let isRadio = $summary.is(':radio')
        let isSelect = $summary.is('select')
        let id = $summary.attr(`${Data.summaryId}`)

        if(summaries[id] === undefined)
            summaries[id] = []

        if(isCheckbox || isRadio) {
            if(!$summary.is(':checked'))
                return

            summaries[id].push($summary.attr(`${Data.summaryText}`))
        } else if(isSelect) {
            let $checked = $summary.find('option:checked')

            if($checked.attr(`${summary}`) === undefined)
                return

            summaries[id].push($checked.attr(`${Data.summaryText}`))
        } else {
            if($summary.val() === '')
                return

            summaries[id].push($summary.val())
        }
    })

    $.each(summaries, (i, a) => {
        let $placeholder = $(`[${Data.summaryPlaceholder}="${i}"]`)
        removeErrors($placeholder);
        $placeholder.html(a.join(', '));
    })
}

const goToStep = (step) => {
    for (let i = 0; i <= step; i++) {
        current = i;
        $currentSlide = $(`${modal} [${Data.slides}='${current}']`)
        updateSummary(i);
    }
}

const removeErrors = (node) => {
    node.parent().find('.js-wizard-validation-error').remove();
}

/* events */

$(document).on('click', `${modal} [${Data.step}]:not(.active):not(.active ~ [${Data.step}])`, function(e) {
    e.preventDefault()

    let slide = parseInt($(this).attr(Data.step))
    showSlide(slide)
})

$(document).on('click', `${modal} [${Data.prev}]`, () => {
    showSlide((current > 0) ? current - 1 : current)
})

$(document).on('click', `${modal} [${Data.next}]`, () => {
    let $inputs = $currentSlide.find(`[${Data.summaryId}]`);

    if ($inputs.length === 0) {
        updateSummary();
        showSlide((current < last) ? current + 1 : last);
    } else {
        let inputsTMP = [];
         $inputs.map(function() {
             inputsTMP.push({ name: this.name, value: this.type === 'checkbox'
                     ? (this.checked ? this.value : '')
                     : this.value});
        });
        startSpinner();
        ioCall('validateStepWizard', [inputsTMP], function (errors) {
            if (errors.length !== 0) {
                $.each(errors, (index, error) => {
                    let $question = $('#question-' + error.questionID);
                    removeErrors($question);
                    $question.parent().addClass('error');
                    $question.parent().append('<div class="error js-wizard-validation-error">' + error.message + '</div>');
                });
            } else {
                updateSummary();
                showSlide((current < last) ? current + 1 : last);
            }
        }).done(function () {
            stopSpinner();
        });
    }

    if (!hasAuth) {
        if ($currentSlide.prop('id') === '3') {
            legalPluginCount = $inputs.serializeArray().length;
        } else if ($currentSlide.prop('id') === '4') {
            paymentPluginCount = $inputs.serializeArray().length;
        }
        if (legalPluginCount > 0 || paymentPluginCount > 0) {
            $auth.removeClass('d-none');
            $summaryPluginNote.removeClass('d-none');
            $summaryPluginNote.next().addClass('d-none');
            $submit.addClass('d-none');
        } else {
            $auth.addClass('d-none');
            $summaryPluginNote.addClass('d-none');
            $summaryPluginNote.next().removeClass('d-none');
            $submit.removeClass('d-none');
        }
    }
});

$(document).on('click', `${modal} input`, function() {
    $(this).parent().removeClass('error');
    removeErrors($(this));
});

$form.on('submit', (e) => {
    e.preventDefault();
    if (!criticalErrors) {
        showSlide((current < last) ? current + 1 : last);
        return;
    }

    $submit.addClass('disabled').attr('disabled', true)
    $prev.addClass('disabled').attr('disabled', true)

    let callback = new Promise((resolve, reject) => {
        startSpinner();

        let checkmark = `<span class="fal fa-check text-success fa-fw"></span>`

        setTimeout(() => {
            $currentSlide.find(`[${Data.step}="1"]`).html(checkmark)
        }, 500)

        setTimeout(() => {
            $currentSlide.find(`[${Data.step}="2"]`).html(checkmark)
        }, 1000)

        setTimeout(() => {
            $currentSlide.find(`[${Data.step}="3"]`).html(checkmark)
        }, 1500)

        setTimeout(() => {
            $currentSlide.find(`[${Data.step}="4"]`).html(checkmark)
        }, 2000)

        setTimeout(() => {
            ioCall('finishWizard', [$form.serializeArray()], function (errors) {
                criticalErrors = false;
                if (errors.length !== 0) {
                    $.each(errors, (index, error) => {
                        let $question = $(`[${Data.summaryPlaceholder}="question-${error.questionID}"]`)
                        removeErrors($question);
                        $question.before('<span class="fa fa-times-circle text-danger mr-2 js-wizard-validation-error" data-toggle="tooltip" data-html="true" title="' + error.message + '"></span>');
                        if (error.critical) {
                            criticalErrors = true;
                        }
                    });
                    $submit.removeClass('disabled').attr('disabled', false)
                    $prev.removeClass('disabled').attr('disabled', false)
                } else {
                    resolve();
                    showSlide((current < last) ? current + 1 : last);
                }
            });
        }, 3000)
    });

    callback.then(() => {
        $submit.removeClass('disabled').attr('disabled', false)
        $prev.removeClass('disabled').attr('disabled', false)
        $modal.addClass('installed')
    }).catch((msg) => {
        $submit.removeClass('disabled').attr('disabled', false)
        $prev.removeClass('disabled').attr('disabled', false)
    })
})

$modal.on('show.bs.modal', () => {
    if (authRedirect) {
        goToStep(authRedirect);
    }
    showSlide(current)
    subsequent = true
})
