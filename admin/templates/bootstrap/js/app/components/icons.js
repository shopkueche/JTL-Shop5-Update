
import { config, dom, library } from '@fortawesome/fontawesome-svg-core'

/* config */

config.searchPseudoElements = true
config.keepOriginalSource = false
config.autoReplaceSvg = 'nest'
config.observeMutations = true

/* light variant */

import {
	faAngleDoubleLeft as falAngleDoubleLeft,
	faMapMarkerQuestion as falMapMarkerQuestion,
	faEdit as falEdit,
	faSearch as falSearch,
	faAngleDown as falAngleDown,
	faTimes as falTimes,
	faPlus as falPlus,
	faClone as falClone,
	faTrashAlt as falTrashAlt,
	faStar as falStar,
	faMapSigns as falMapSigns,
	faCog as falCog,
	faPlusCircle as falPlusCircle,
	faUsers as falUsers,
	faUserSecret as falUserSecret,
	faCheck as falCheck,
	faExclamationTriangle as falExclamationTriangle,
	faHandHoldingUsd as falHandHoldingUsd,
	faInfoCircle as falInfoCircle,
	faUpload as falUpload,
	faLongArrowRight as falLongArrowRight,
	faLongArrowLeft as falLongArrowLeft,
	faCheckSquare as falCheckSquare,
	faSquare as falSquare,
	faEraser as falEraser,
	faDownload as falDownload
} from '@fortawesome/pro-light-svg-icons'

/* solid variant */

import {
	faStar as fasStar,
	faEdit as fasEdit,
	faBell as fasBell,
	faCircle as fasCircle,
	faUser as fasUser,
	faPlus as fasPlus,
	faMinus as fasMinus,
	faPlusCircle as fasPlusCircle,
	faCog as fasCog,
	faHandHoldingUsd as fasHandHoldingUsd,
	faExclamationTriangle as fasExclamationTriangle,
	faClone as fasClone,
	faTrashAlt as fasTrashAlt,
	faTimes as fasTimes,
	faAngleDown as fasAngleDown,
	faInfoCircle as fasInfoCircle,
	faLongArrowRight as fasLongArrowRight,
	faSearch as fasSearch,
	faUndoAlt as fasUndoAlt,
	faDownload as fasDownload,
	faCheck as fasCheck
} from '@fortawesome/pro-solid-svg-icons'


library.add(
	falAngleDoubleLeft, falMapMarkerQuestion, fasBell, falEdit,
	falSearch, falAngleDown, fasStar, fasCircle, fasUser, falTimes,
	falPlus, falClone, falTrashAlt, falStar, falMapSigns, falCog,
	falPlusCircle, falUsers, falUserSecret, fasPlusCircle, fasCog,
	falCheck, falExclamationTriangle, falHandHoldingUsd, falDownload, fasEdit,
	fasHandHoldingUsd, falClone, fasClone, fasTrashAlt, fasTimes, fasMinus,
	fasAngleDown, fasInfoCircle, falInfoCircle, falUpload, falLongArrowRight,
	fasLongArrowRight, falCheckSquare, falSquare, falLongArrowLeft,
	fasSearch, falEraser, fasUndoAlt, fasDownload, fasPlus, fasCheck, fasExclamationTriangle
)

dom.watch()
