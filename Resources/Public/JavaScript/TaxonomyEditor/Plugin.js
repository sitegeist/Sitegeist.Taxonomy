/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 9);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;
function readFromConsumerApi(key) {
    return function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        var _a;
        if (window['@Neos:HostPluginAPI'] && window['@Neos:HostPluginAPI']["@" + key]) {
            return (_a = window['@Neos:HostPluginAPI'])["@" + key].apply(_a, args);
        }
        throw new Error("You are trying to read from a consumer api that hasn't been initialized yet!");
    };
}
exports["default"] = readFromConsumerApi;
//# sourceMappingURL=readFromConsumerApi.js.map

/***/ }),
/* 1 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony export (immutable) */ __webpack_exports__["__extends"] = __extends;
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "__assign", function() { return __assign; });
/* harmony export (immutable) */ __webpack_exports__["__rest"] = __rest;
/* harmony export (immutable) */ __webpack_exports__["__decorate"] = __decorate;
/* harmony export (immutable) */ __webpack_exports__["__param"] = __param;
/* harmony export (immutable) */ __webpack_exports__["__metadata"] = __metadata;
/* harmony export (immutable) */ __webpack_exports__["__awaiter"] = __awaiter;
/* harmony export (immutable) */ __webpack_exports__["__generator"] = __generator;
/* harmony export (immutable) */ __webpack_exports__["__exportStar"] = __exportStar;
/* harmony export (immutable) */ __webpack_exports__["__values"] = __values;
/* harmony export (immutable) */ __webpack_exports__["__read"] = __read;
/* harmony export (immutable) */ __webpack_exports__["__spread"] = __spread;
/* harmony export (immutable) */ __webpack_exports__["__await"] = __await;
/* harmony export (immutable) */ __webpack_exports__["__asyncGenerator"] = __asyncGenerator;
/* harmony export (immutable) */ __webpack_exports__["__asyncDelegator"] = __asyncDelegator;
/* harmony export (immutable) */ __webpack_exports__["__asyncValues"] = __asyncValues;
/* harmony export (immutable) */ __webpack_exports__["__makeTemplateObject"] = __makeTemplateObject;
/* harmony export (immutable) */ __webpack_exports__["__importStar"] = __importStar;
/* harmony export (immutable) */ __webpack_exports__["__importDefault"] = __importDefault;
/*! *****************************************************************************
Copyright (c) Microsoft Corporation. All rights reserved.
Licensed under the Apache License, Version 2.0 (the "License"); you may not use
this file except in compliance with the License. You may obtain a copy of the
License at http://www.apache.org/licenses/LICENSE-2.0

THIS CODE IS PROVIDED ON AN *AS IS* BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
KIND, EITHER EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION ANY IMPLIED
WARRANTIES OR CONDITIONS OF TITLE, FITNESS FOR A PARTICULAR PURPOSE,
MERCHANTABLITY OR NON-INFRINGEMENT.

See the Apache Version 2.0 License for specific language governing permissions
and limitations under the License.
***************************************************************************** */
/* global Reflect, Promise */

var extendStatics = function(d, b) {
    extendStatics = Object.setPrototypeOf ||
        ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
        function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
    return extendStatics(d, b);
};

function __extends(d, b) {
    extendStatics(d, b);
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
}

var __assign = function() {
    __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p)) t[p] = s[p];
        }
        return t;
    }
    return __assign.apply(this, arguments);
}

function __rest(s, e) {
    var t = {};
    for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p) && e.indexOf(p) < 0)
        t[p] = s[p];
    if (s != null && typeof Object.getOwnPropertySymbols === "function")
        for (var i = 0, p = Object.getOwnPropertySymbols(s); i < p.length; i++) if (e.indexOf(p[i]) < 0)
            t[p[i]] = s[p[i]];
    return t;
}

function __decorate(decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
}

function __param(paramIndex, decorator) {
    return function (target, key) { decorator(target, key, paramIndex); }
}

function __metadata(metadataKey, metadataValue) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(metadataKey, metadataValue);
}

function __awaiter(thisArg, _arguments, P, generator) {
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : new P(function (resolve) { resolve(result.value); }).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
}

function __generator(thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g;
    return g = { next: verb(0), "throw": verb(1), "return": verb(2) }, typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (_) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
}

function __exportStar(m, exports) {
    for (var p in m) if (!exports.hasOwnProperty(p)) exports[p] = m[p];
}

function __values(o) {
    var m = typeof Symbol === "function" && o[Symbol.iterator], i = 0;
    if (m) return m.call(o);
    return {
        next: function () {
            if (o && i >= o.length) o = void 0;
            return { value: o && o[i++], done: !o };
        }
    };
}

function __read(o, n) {
    var m = typeof Symbol === "function" && o[Symbol.iterator];
    if (!m) return o;
    var i = m.call(o), r, ar = [], e;
    try {
        while ((n === void 0 || n-- > 0) && !(r = i.next()).done) ar.push(r.value);
    }
    catch (error) { e = { error: error }; }
    finally {
        try {
            if (r && !r.done && (m = i["return"])) m.call(i);
        }
        finally { if (e) throw e.error; }
    }
    return ar;
}

function __spread() {
    for (var ar = [], i = 0; i < arguments.length; i++)
        ar = ar.concat(__read(arguments[i]));
    return ar;
}

function __await(v) {
    return this instanceof __await ? (this.v = v, this) : new __await(v);
}

function __asyncGenerator(thisArg, _arguments, generator) {
    if (!Symbol.asyncIterator) throw new TypeError("Symbol.asyncIterator is not defined.");
    var g = generator.apply(thisArg, _arguments || []), i, q = [];
    return i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function () { return this; }, i;
    function verb(n) { if (g[n]) i[n] = function (v) { return new Promise(function (a, b) { q.push([n, v, a, b]) > 1 || resume(n, v); }); }; }
    function resume(n, v) { try { step(g[n](v)); } catch (e) { settle(q[0][3], e); } }
    function step(r) { r.value instanceof __await ? Promise.resolve(r.value.v).then(fulfill, reject) : settle(q[0][2], r); }
    function fulfill(value) { resume("next", value); }
    function reject(value) { resume("throw", value); }
    function settle(f, v) { if (f(v), q.shift(), q.length) resume(q[0][0], q[0][1]); }
}

function __asyncDelegator(o) {
    var i, p;
    return i = {}, verb("next"), verb("throw", function (e) { throw e; }), verb("return"), i[Symbol.iterator] = function () { return this; }, i;
    function verb(n, f) { i[n] = o[n] ? function (v) { return (p = !p) ? { value: __await(o[n](v)), done: n === "return" } : f ? f(v) : v; } : f; }
}

function __asyncValues(o) {
    if (!Symbol.asyncIterator) throw new TypeError("Symbol.asyncIterator is not defined.");
    var m = o[Symbol.asyncIterator], i;
    return m ? m.call(o) : (o = typeof __values === "function" ? __values(o) : o[Symbol.iterator](), i = {}, verb("next"), verb("throw"), verb("return"), i[Symbol.asyncIterator] = function () { return this; }, i);
    function verb(n) { i[n] = o[n] && function (v) { return new Promise(function (resolve, reject) { v = o[n](v), settle(resolve, reject, v.done, v.value); }); }; }
    function settle(resolve, reject, d, v) { Promise.resolve(v).then(function(v) { resolve({ value: v, done: d }); }, reject); }
}

function __makeTemplateObject(cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};

function __importStar(mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (Object.hasOwnProperty.call(mod, k)) result[k] = mod[k];
    result.default = mod;
    return result;
}

function __importDefault(mod) {
    return (mod && mod.__esModule) ? mod : { default: mod };
}


/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;
var tslib_1 = __webpack_require__(1);
var AbstractRegistry_1 = tslib_1.__importDefault(__webpack_require__(15));
var positional_array_sorter_1 = tslib_1.__importDefault(__webpack_require__(16));
var SynchronousRegistry = function (_super) {
    tslib_1.__extends(SynchronousRegistry, _super);
    function SynchronousRegistry(description) {
        var _this = _super.call(this, description) || this;
        _this._registry = [];
        return _this;
    }
    SynchronousRegistry.prototype.set = function (key, value, position) {
        if (position === void 0) {
            position = 0;
        }
        if (typeof key !== 'string') {
            throw new Error('Key must be a string');
        }
        if (typeof position !== 'string' && typeof position !== 'number') {
            throw new Error('Position must be a string or a number');
        }
        var entry = { key: key, value: value };
        if (position) {
            entry.position = position;
        }
        var indexOfItemWithTheSameKey = this._registry.findIndex(function (item) {
            return item.key === key;
        });
        if (indexOfItemWithTheSameKey === -1) {
            this._registry.push(entry);
        } else {
            this._registry[indexOfItemWithTheSameKey] = entry;
        }
        return value;
    };
    SynchronousRegistry.prototype.get = function (key) {
        if (typeof key !== 'string') {
            console.error('Key must be a string');
            return null;
        }
        var result = this._registry.find(function (item) {
            return item.key === key;
        });
        return result ? result.value : null;
    };
    SynchronousRegistry.prototype._getChildrenWrapped = function (searchKey) {
        var unsortedChildren = this._registry.filter(function (item) {
            return item.key.indexOf(searchKey + '/') === 0;
        });
        return positional_array_sorter_1["default"](unsortedChildren);
    };
    SynchronousRegistry.prototype.getChildrenAsObject = function (searchKey) {
        var result = {};
        this._getChildrenWrapped(searchKey).forEach(function (item) {
            result[item.key] = item.value;
        });
        return result;
    };
    SynchronousRegistry.prototype.getChildren = function (searchKey) {
        return this._getChildrenWrapped(searchKey).map(function (item) {
            return item.value;
        });
    };
    SynchronousRegistry.prototype.has = function (key) {
        if (typeof key !== 'string') {
            console.error('Key must be a string');
            return false;
        }
        return Boolean(this._registry.find(function (item) {
            return item.key === key;
        }));
    };
    SynchronousRegistry.prototype._getAllWrapped = function () {
        return positional_array_sorter_1["default"](this._registry);
    };
    SynchronousRegistry.prototype.getAllAsObject = function () {
        var result = {};
        this._getAllWrapped().forEach(function (item) {
            result[item.key] = item.value;
        });
        return result;
    };
    SynchronousRegistry.prototype.getAllAsList = function () {
        return this._getAllWrapped().map(function (item) {
            return Object.assign({ id: item.key }, item.value);
        });
    };
    return SynchronousRegistry;
}(AbstractRegistry_1["default"]);
exports["default"] = SynchronousRegistry;
//# sourceMappingURL=SynchronousRegistry.js.map

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('vendor')().React;

/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('vendor')().PropTypes;

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('NeosProjectPackages')().NeosUiDecorators;

/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('NeosProjectPackages')().ReactUiComponents;

/***/ }),
/* 7 */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function(useSourceMap) {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		return this.map(function (item) {
			var content = cssWithMappingToString(item, useSourceMap);
			if(item[2]) {
				return "@media " + item[2] + "{" + content + "}";
			} else {
				return content;
			}
		}).join("");
	};

	// import a list of modules into the list
	list.i = function(modules, mediaQuery) {
		if(typeof modules === "string")
			modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for(var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if(typeof id === "number")
				alreadyImportedModules[id] = true;
		}
		for(i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if(mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if(mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};

function cssWithMappingToString(item, useSourceMap) {
	var content = item[1] || '';
	var cssMapping = item[3];
	if (!cssMapping) {
		return content;
	}

	if (useSourceMap && typeof btoa === 'function') {
		var sourceMapping = toComment(cssMapping);
		var sourceURLs = cssMapping.sources.map(function (source) {
			return '/*# sourceURL=' + cssMapping.sourceRoot + source + ' */'
		});

		return [content].concat(sourceURLs).concat([sourceMapping]).join('\n');
	}

	return [content].join('\n');
}

// Adapted from convert-source-map (MIT)
function toComment(sourceMap) {
	// eslint-disable-next-line no-undef
	var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));
	var data = 'sourceMappingURL=data:application/json;charset=utf-8;base64,' + base64;

	return '/*# ' + data + ' */';
}


/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/

var stylesInDom = {};

var	memoize = function (fn) {
	var memo;

	return function () {
		if (typeof memo === "undefined") memo = fn.apply(this, arguments);
		return memo;
	};
};

var isOldIE = memoize(function () {
	// Test for IE <= 9 as proposed by Browserhacks
	// @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
	// Tests for existence of standard globals is to allow style-loader
	// to operate correctly into non-standard environments
	// @see https://github.com/webpack-contrib/style-loader/issues/177
	return window && document && document.all && !window.atob;
});

var getTarget = function (target) {
  return document.querySelector(target);
};

var getElement = (function (fn) {
	var memo = {};

	return function(target) {
                // If passing function in options, then use it for resolve "head" element.
                // Useful for Shadow Root style i.e
                // {
                //   insertInto: function () { return document.querySelector("#foo").shadowRoot }
                // }
                if (typeof target === 'function') {
                        return target();
                }
                if (typeof memo[target] === "undefined") {
			var styleTarget = getTarget.call(this, target);
			// Special case to return head of iframe instead of iframe itself
			if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
				try {
					// This will throw an exception if access to iframe is blocked
					// due to cross-origin restrictions
					styleTarget = styleTarget.contentDocument.head;
				} catch(e) {
					styleTarget = null;
				}
			}
			memo[target] = styleTarget;
		}
		return memo[target]
	};
})();

var singleton = null;
var	singletonCounter = 0;
var	stylesInsertedAtTop = [];

var	fixUrls = __webpack_require__(22);

module.exports = function(list, options) {
	if (typeof DEBUG !== "undefined" && DEBUG) {
		if (typeof document !== "object") throw new Error("The style-loader cannot be used in a non-browser environment");
	}

	options = options || {};

	options.attrs = typeof options.attrs === "object" ? options.attrs : {};

	// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
	// tags it will allow on a page
	if (!options.singleton && typeof options.singleton !== "boolean") options.singleton = isOldIE();

	// By default, add <style> tags to the <head> element
        if (!options.insertInto) options.insertInto = "head";

	// By default, add <style> tags to the bottom of the target
	if (!options.insertAt) options.insertAt = "bottom";

	var styles = listToStyles(list, options);

	addStylesToDom(styles, options);

	return function update (newList) {
		var mayRemove = [];

		for (var i = 0; i < styles.length; i++) {
			var item = styles[i];
			var domStyle = stylesInDom[item.id];

			domStyle.refs--;
			mayRemove.push(domStyle);
		}

		if(newList) {
			var newStyles = listToStyles(newList, options);
			addStylesToDom(newStyles, options);
		}

		for (var i = 0; i < mayRemove.length; i++) {
			var domStyle = mayRemove[i];

			if(domStyle.refs === 0) {
				for (var j = 0; j < domStyle.parts.length; j++) domStyle.parts[j]();

				delete stylesInDom[domStyle.id];
			}
		}
	};
};

function addStylesToDom (styles, options) {
	for (var i = 0; i < styles.length; i++) {
		var item = styles[i];
		var domStyle = stylesInDom[item.id];

		if(domStyle) {
			domStyle.refs++;

			for(var j = 0; j < domStyle.parts.length; j++) {
				domStyle.parts[j](item.parts[j]);
			}

			for(; j < item.parts.length; j++) {
				domStyle.parts.push(addStyle(item.parts[j], options));
			}
		} else {
			var parts = [];

			for(var j = 0; j < item.parts.length; j++) {
				parts.push(addStyle(item.parts[j], options));
			}

			stylesInDom[item.id] = {id: item.id, refs: 1, parts: parts};
		}
	}
}

function listToStyles (list, options) {
	var styles = [];
	var newStyles = {};

	for (var i = 0; i < list.length; i++) {
		var item = list[i];
		var id = options.base ? item[0] + options.base : item[0];
		var css = item[1];
		var media = item[2];
		var sourceMap = item[3];
		var part = {css: css, media: media, sourceMap: sourceMap};

		if(!newStyles[id]) styles.push(newStyles[id] = {id: id, parts: [part]});
		else newStyles[id].parts.push(part);
	}

	return styles;
}

function insertStyleElement (options, style) {
	var target = getElement(options.insertInto)

	if (!target) {
		throw new Error("Couldn't find a style target. This probably means that the value for the 'insertInto' parameter is invalid.");
	}

	var lastStyleElementInsertedAtTop = stylesInsertedAtTop[stylesInsertedAtTop.length - 1];

	if (options.insertAt === "top") {
		if (!lastStyleElementInsertedAtTop) {
			target.insertBefore(style, target.firstChild);
		} else if (lastStyleElementInsertedAtTop.nextSibling) {
			target.insertBefore(style, lastStyleElementInsertedAtTop.nextSibling);
		} else {
			target.appendChild(style);
		}
		stylesInsertedAtTop.push(style);
	} else if (options.insertAt === "bottom") {
		target.appendChild(style);
	} else if (typeof options.insertAt === "object" && options.insertAt.before) {
		var nextSibling = getElement(options.insertInto + " " + options.insertAt.before);
		target.insertBefore(style, nextSibling);
	} else {
		throw new Error("[Style Loader]\n\n Invalid value for parameter 'insertAt' ('options.insertAt') found.\n Must be 'top', 'bottom', or Object.\n (https://github.com/webpack-contrib/style-loader#insertat)\n");
	}
}

function removeStyleElement (style) {
	if (style.parentNode === null) return false;
	style.parentNode.removeChild(style);

	var idx = stylesInsertedAtTop.indexOf(style);
	if(idx >= 0) {
		stylesInsertedAtTop.splice(idx, 1);
	}
}

function createStyleElement (options) {
	var style = document.createElement("style");

	if(options.attrs.type === undefined) {
		options.attrs.type = "text/css";
	}

	addAttrs(style, options.attrs);
	insertStyleElement(options, style);

	return style;
}

function createLinkElement (options) {
	var link = document.createElement("link");

	if(options.attrs.type === undefined) {
		options.attrs.type = "text/css";
	}
	options.attrs.rel = "stylesheet";

	addAttrs(link, options.attrs);
	insertStyleElement(options, link);

	return link;
}

function addAttrs (el, attrs) {
	Object.keys(attrs).forEach(function (key) {
		el.setAttribute(key, attrs[key]);
	});
}

function addStyle (obj, options) {
	var style, update, remove, result;

	// If a transform function was defined, run it on the css
	if (options.transform && obj.css) {
	    result = options.transform(obj.css);

	    if (result) {
	    	// If transform returns a value, use that instead of the original css.
	    	// This allows running runtime transformations on the css.
	    	obj.css = result;
	    } else {
	    	// If the transform function returns a falsy value, don't add this css.
	    	// This allows conditional loading of css
	    	return function() {
	    		// noop
	    	};
	    }
	}

	if (options.singleton) {
		var styleIndex = singletonCounter++;

		style = singleton || (singleton = createStyleElement(options));

		update = applyToSingletonTag.bind(null, style, styleIndex, false);
		remove = applyToSingletonTag.bind(null, style, styleIndex, true);

	} else if (
		obj.sourceMap &&
		typeof URL === "function" &&
		typeof URL.createObjectURL === "function" &&
		typeof URL.revokeObjectURL === "function" &&
		typeof Blob === "function" &&
		typeof btoa === "function"
	) {
		style = createLinkElement(options);
		update = updateLink.bind(null, style, options);
		remove = function () {
			removeStyleElement(style);

			if(style.href) URL.revokeObjectURL(style.href);
		};
	} else {
		style = createStyleElement(options);
		update = applyToTag.bind(null, style);
		remove = function () {
			removeStyleElement(style);
		};
	}

	update(obj);

	return function updateStyle (newObj) {
		if (newObj) {
			if (
				newObj.css === obj.css &&
				newObj.media === obj.media &&
				newObj.sourceMap === obj.sourceMap
			) {
				return;
			}

			update(obj = newObj);
		} else {
			remove();
		}
	};
}

var replaceText = (function () {
	var textStore = [];

	return function (index, replacement) {
		textStore[index] = replacement;

		return textStore.filter(Boolean).join('\n');
	};
})();

function applyToSingletonTag (style, index, remove, obj) {
	var css = remove ? "" : obj.css;

	if (style.styleSheet) {
		style.styleSheet.cssText = replaceText(index, css);
	} else {
		var cssNode = document.createTextNode(css);
		var childNodes = style.childNodes;

		if (childNodes[index]) style.removeChild(childNodes[index]);

		if (childNodes.length) {
			style.insertBefore(cssNode, childNodes[index]);
		} else {
			style.appendChild(cssNode);
		}
	}
}

function applyToTag (style, obj) {
	var css = obj.css;
	var media = obj.media;

	if(media) {
		style.setAttribute("media", media)
	}

	if(style.styleSheet) {
		style.styleSheet.cssText = css;
	} else {
		while(style.firstChild) {
			style.removeChild(style.firstChild);
		}

		style.appendChild(document.createTextNode(css));
	}
}

function updateLink (link, options, obj) {
	var css = obj.css;
	var sourceMap = obj.sourceMap;

	/*
		If convertToAbsoluteUrls isn't defined, but sourcemaps are enabled
		and there is no publicPath defined then lets turn convertToAbsoluteUrls
		on by default.  Otherwise default to the convertToAbsoluteUrls option
		directly
	*/
	var autoFixUrls = options.convertToAbsoluteUrls === undefined && sourceMap;

	if (options.convertToAbsoluteUrls || autoFixUrls) {
		css = fixUrls(css);
	}

	if (sourceMap) {
		// http://stackoverflow.com/a/26603875
		css += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + " */";
	}

	var blob = new Blob([css], { type: "text/css" });

	var oldSrc = link.href;

	link.href = URL.createObjectURL(blob);

	if(oldSrc) URL.revokeObjectURL(oldSrc);
}


/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


__webpack_require__(10);

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _neosUiExtensibility = __webpack_require__(11);

var _neosUiExtensibility2 = _interopRequireDefault(_neosUiExtensibility);

var _TaxonomyEditor = __webpack_require__(18);

var _TaxonomyEditor2 = _interopRequireDefault(_TaxonomyEditor);

var _TaxonomyTreeSelect = __webpack_require__(23);

var _TaxonomyTreeSelect2 = _interopRequireDefault(_TaxonomyTreeSelect);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

(0, _neosUiExtensibility2.default)('Sitegeist.Taxonomy:TaxonomyEditor', {}, function (globalRegistry) {
  var editorsRegistry = globalRegistry.get('inspector').get('editors');
  var secondaryEditorsRegistry = globalRegistry.get('inspector').get('secondaryEditors');

  editorsRegistry.set('Sitegeist.Taxonomy:TaxonomyEditor', {
    component: _TaxonomyEditor2.default
  });

  secondaryEditorsRegistry.set('Sitegeist.Taxonomy:TaxonomyTreeSelect', {
    component: _TaxonomyTreeSelect2.default
  });
});

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;
var tslib_1 = __webpack_require__(1);
var createConsumerApi_1 = tslib_1.__importDefault(__webpack_require__(12));
exports.createConsumerApi = createConsumerApi_1["default"];
var readFromConsumerApi_1 = tslib_1.__importDefault(__webpack_require__(0));
exports.readFromConsumerApi = readFromConsumerApi_1["default"];
var index_1 = __webpack_require__(14);
exports.SynchronousRegistry = index_1.SynchronousRegistry;
exports.SynchronousMetaRegistry = index_1.SynchronousMetaRegistry;
exports["default"] = readFromConsumerApi_1["default"]('manifest');
//# sourceMappingURL=index.js.map

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;
var tslib_1 = __webpack_require__(1);
var manifest_1 = tslib_1.__importDefault(__webpack_require__(13));
var createReadOnlyValue = function createReadOnlyValue(value) {
    return {
        value: value,
        writable: false,
        enumerable: false,
        configurable: true
    };
};
function createConsumerApi(manifests, exposureMap) {
    var api = {};
    Object.keys(exposureMap).forEach(function (key) {
        Object.defineProperty(api, key, createReadOnlyValue(exposureMap[key]));
    });
    Object.defineProperty(api, '@manifest', createReadOnlyValue(manifest_1["default"](manifests)));
    Object.defineProperty(window, '@Neos:HostPluginAPI', createReadOnlyValue(api));
}
exports["default"] = createConsumerApi;
//# sourceMappingURL=createConsumerApi.js.map

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;
exports["default"] = function (manifests) {
    return function (identifier, options, bootstrap) {
        var _a;
        manifests.push((_a = {}, _a[identifier] = {
            options: options,
            bootstrap: bootstrap
        }, _a));
    };
};
//# sourceMappingURL=manifest.js.map

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;
var tslib_1 = __webpack_require__(1);
var SynchronousRegistry_1 = tslib_1.__importDefault(__webpack_require__(2));
exports.SynchronousRegistry = SynchronousRegistry_1["default"];
var SynchronousMetaRegistry_1 = tslib_1.__importDefault(__webpack_require__(17));
exports.SynchronousMetaRegistry = SynchronousMetaRegistry_1["default"];
//# sourceMappingURL=index.js.map

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;
var AbstractRegistry = function () {
    function AbstractRegistry(description) {
        this.SERIAL_VERSION_UID = 'd8a5aa78-978e-11e6-ae22-56b6b6499611';
        this.description = description;
    }
    return AbstractRegistry;
}();
exports["default"] = AbstractRegistry;
//# sourceMappingURL=AbstractRegistry.js.map

/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;
var isOriginal = function isOriginal(value) {
    return value && value.indexOf && value.indexOf('_original_') === 0;
};
var getOriginal = function getOriginal(value) {
    return value && value.substring && Number(value.substring(10));
};
var positionalArraySorter = function positionalArraySorter(subject, position, idKey) {
    if (position === void 0) {
        position = 'position';
    }
    if (idKey === void 0) {
        idKey = 'key';
    }
    var positionAccessor = typeof position === 'string' ? function (value) {
        return value[position];
    } : position;
    var positionsArray = subject.map(function (value, index) {
        var position = positionAccessor(value);
        return position === undefined ? "_original_" + index : position;
    });
    var validKeys = subject.map(function (value) {
        return idKey in value && value[idKey];
    }).filter(function (i) {
        return i;
    }).map(function (i) {
        return String(i);
    });
    var middleKeys = [];
    var startKeys = [];
    var endKeys = [];
    var beforeKeys = [];
    var afterKeys = [];
    var corruptKeys = [];
    positionsArray.forEach(function (value, index) {
        if (isNaN(value) === false || isOriginal(value)) {
            middleKeys.push([index, value]);
        } else if (typeof value === 'string') {
            if (value.includes('start')) {
                var weightMatch = value.match(/start\s+(\d+)/);
                var weight = weightMatch && weightMatch[1] || 0;
                startKeys.push([index, Number(weight)]);
            } else if (value.includes('end')) {
                var weightMatch = value.match(/end\s+(\d+)/);
                var weight = weightMatch && weightMatch[1] || 0;
                endKeys.push([index, Number(weight)]);
            } else if (value.includes('before')) {
                var keyMatch = value.match(/before\s+(\S+)/);
                var key = keyMatch && keyMatch[1];
                if (key && validKeys.includes(key)) {
                    beforeKeys.push([index, key]);
                } else {
                    corruptKeys.push(index);
                    console.warn('The following position value is corrupt: %s', value);
                }
            } else if (value.includes('after')) {
                var keyMatch = value.match(/after\s+(\S+)/);
                var key = keyMatch && keyMatch[1];
                if (key && validKeys.includes(key)) {
                    afterKeys.push([index, key]);
                } else {
                    corruptKeys.push(index);
                    console.warn('The following position value is corrupt: %s', value);
                }
            } else {
                corruptKeys.push(index);
                console.warn('The following position value is corrupt: %s', value);
            }
        } else {
            corruptKeys.push(index);
            console.warn('The following position value is corrupt: %s', value);
        }
    });
    var sortByWeightFunc = function sortByWeightFunc(a, b) {
        return a[1] - b[1];
    };
    var sortWithRetainingOriginalPos = function sortWithRetainingOriginalPos(_a, _b) {
        var a = _a[1];
        var b = _b[1];
        if (isOriginal(a) && isOriginal(b)) {
            return getOriginal(a) - getOriginal(b);
        }
        if (typeof a === 'string' && a.includes && a.includes('_original_')) {
            return 1;
        }
        if (typeof b === 'string' && b.includes && b.includes('_original_')) {
            return -1;
        }
        return Number(a) - Number(b);
    };
    var sortedIndexes = startKeys.sort(sortByWeightFunc).map(function (pair) {
        return pair[0];
    }).concat(middleKeys.sort(sortWithRetainingOriginalPos).map(function (pair) {
        return pair[0];
    }), corruptKeys, endKeys.sort(sortByWeightFunc).map(function (pair) {
        return pair[0];
    }));
    var _loop_1 = function _loop_1() {
        var alteredNumber = 0;
        beforeKeys.forEach(function (pair, index) {
            var targetIndexInSubject = subject.findIndex(function (item) {
                return String(item[idKey]) === pair[1];
            });
            var indexInIndexes = sortedIndexes.findIndex(function (item) {
                return item === targetIndexInSubject;
            });
            if (indexInIndexes !== -1) {
                sortedIndexes.splice(indexInIndexes, 0, pair[0]);
                beforeKeys.splice(index, 1);
                alteredNumber++;
            }
        });
        afterKeys.forEach(function (pair, index) {
            var targetIndexInSubject = subject.findIndex(function (item) {
                return String(item[idKey]) === pair[1];
            });
            var indexInIndexes = sortedIndexes.findIndex(function (item) {
                return item === targetIndexInSubject;
            });
            if (indexInIndexes !== -1) {
                sortedIndexes.splice(indexInIndexes + 1, 0, pair[0]);
                afterKeys.splice(index, 1);
                alteredNumber++;
            }
        });
        if (alteredNumber === 0) {
            console.warn('Circular reference detected. Append broken entries at the end.');
            sortedIndexes = sortedIndexes.concat(beforeKeys.map(function (pair) {
                return pair[0];
            }), afterKeys.map(function (pair) {
                return pair[0];
            }));
            return "break";
        }
    };
    while (beforeKeys.length > 0 || afterKeys.length > 0) {
        var state_1 = _loop_1();
        if (state_1 === "break") break;
    }
    return sortedIndexes.map(function (index) {
        return subject[index];
    });
};
exports["default"] = positionalArraySorter;
//# sourceMappingURL=positionalArraySorter.js.map

/***/ }),
/* 17 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;
var tslib_1 = __webpack_require__(1);
var SynchronousRegistry_1 = tslib_1.__importDefault(__webpack_require__(2));
var SynchronousMetaRegistry = function (_super) {
    tslib_1.__extends(SynchronousMetaRegistry, _super);
    function SynchronousMetaRegistry() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    SynchronousMetaRegistry.prototype.set = function (key, value) {
        if (value.SERIAL_VERSION_UID !== 'd8a5aa78-978e-11e6-ae22-56b6b6499611') {
            throw new Error('You can only add registries to a meta registry');
        }
        return _super.prototype.set.call(this, key, value);
    };
    return SynchronousMetaRegistry;
}(SynchronousRegistry_1["default"]);
exports["default"] = SynchronousMetaRegistry;
//# sourceMappingURL=SynchronousMetaRegistry.js.map

/***/ }),
/* 18 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = undefined;

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _dec, _class, _class2, _temp2;

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _reactDom = __webpack_require__(19);

var _propTypes = __webpack_require__(4);

var _propTypes2 = _interopRequireDefault(_propTypes);

var _neosUiDecorators = __webpack_require__(5);

var _reactUiComponents = __webpack_require__(6);

var _TaxonomyEditor = __webpack_require__(20);

var _TaxonomyEditor2 = _interopRequireDefault(_TaxonomyEditor);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var TaxonomyEditor = (_dec = (0, _neosUiDecorators.neos)(function (globalRegistry) {
	var secondaryEditorsRegistry = globalRegistry.get('inspector').get('secondaryEditors');
	var editorsRegistry = globalRegistry.get('inspector').get('editors');

	var _editorsRegistry$get = editorsRegistry.get('Neos.Neos/Inspector/Editors/ReferencesEditor'),
	    ReferencesEditor = _editorsRegistry$get.component;

	var _secondaryEditorsRegi = secondaryEditorsRegistry.get('Sitegeist.Taxonomy:TaxonomyTreeSelect'),
	    TaxonomyTreeSelect = _secondaryEditorsRegi.component;

	return {
		ReferencesEditor: ReferencesEditor,
		TaxonomyTreeSelect: TaxonomyTreeSelect
	};
}), _dec(_class = (_temp2 = _class2 = function (_PureComponent) {
	_inherits(TaxonomyEditor, _PureComponent);

	function TaxonomyEditor() {
		var _ref;

		var _temp, _this, _ret;

		_classCallCheck(this, TaxonomyEditor);

		for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
			args[_key] = arguments[_key];
		}

		return _ret = (_temp = (_this = _possibleConstructorReturn(this, (_ref = TaxonomyEditor.__proto__ || Object.getPrototypeOf(TaxonomyEditor)).call.apply(_ref, [this].concat(args))), _this), _this.state = {
			secondaryInspectorPortal: null,
			openTaxonomyBranchesInSecondaryInspector: null
		}, _this.handleCloseSecondaryScreen = function () {
			var renderSecondaryInspector = _this.props.renderSecondaryInspector;

			renderSecondaryInspector(undefined, undefined);
		}, _this.handleOpenSecondaryScreen = function () {
			var _this$props = _this.props,
			    TaxonomyTreeSelect = _this$props.TaxonomyTreeSelect,
			    renderSecondaryInspector = _this$props.renderSecondaryInspector;

			renderSecondaryInspector('TAXONOMY_TREE_SELECT', function () {
				return _react2.default.createElement('div', { ref: function ref(secondaryInspectorPortal) {
						return _this.setState({ secondaryInspectorPortal: secondaryInspectorPortal });
					} });
			});
		}, _this.handleToggleTaxonomyInSecondaryInspector = function (taxonomyIdentifier) {
			var value = Array.isArray(_this.props.value) ? _this.props.value : [];
			var commit = _this.props.commit;


			if (value.includes(taxonomyIdentifier)) {
				commit(value.filter(function (item) {
					return item !== taxonomyIdentifier;
				}));
			} else {
				commit([].concat(_toConsumableArray(value), [taxonomyIdentifier]));
			}
		}, _this.handleToggleTaxonomyBranchInSecondaryInspector = function (taxonomyIdentifier) {
			return _this.setState(function (state) {
				if (state.openTaxonomyBranchesInSecondaryInspector !== null) {
					if (state.openTaxonomyBranchesInSecondaryInspector.includes(taxonomyIdentifier)) {
						return {
							openTaxonomyBranchesInSecondaryInspector: state.openTaxonomyBranchesInSecondaryInspector.filter(function (item) {
								return item !== taxonomyIdentifier;
							})
						};
					} else {
						return {
							openTaxonomyBranchesInSecondaryInspector: [].concat(_toConsumableArray(state.openTaxonomyBranchesInSecondaryInspector), [taxonomyIdentifier])
						};
					}
				}
			});
		}, _this.handleInitializeTaxonomyBranchesInSecondaryInspector = function (taxonomyIdentifiers) {
			return _this.setState(function (state) {
				if (state.openTaxonomyBranchesInSecondaryInspector === null) {
					return {
						openTaxonomyBranchesInSecondaryInspector: taxonomyIdentifiers
					};
				}
			});
		}, _this.handleCommit = function (value) {
			var commit = _this.props.commit;


			commit(value);
		}, _temp), _possibleConstructorReturn(_this, _ret);
	}

	_createClass(TaxonomyEditor, [{
		key: 'componentWillUnmount',
		value: function componentWillUnmount() {
			this.handleCloseSecondaryScreen();
		}
	}, {
		key: 'render',
		value: function render() {
			var _props = this.props,
			    ReferencesEditor = _props.ReferencesEditor,
			    TaxonomyTreeSelect = _props.TaxonomyTreeSelect,
			    value = _props.value,
			    identifier = _props.identifier,
			    options = _props.options;
			var _state = this.state,
			    secondaryInspectorPortal = _state.secondaryInspectorPortal,
			    openTaxonomyBranchesInSecondaryInspector = _state.openTaxonomyBranchesInSecondaryInspector;


			return _react2.default.createElement(
				'div',
				{ className: _TaxonomyEditor2.default.taxonomyEditor },
				_react2.default.createElement(ReferencesEditor, _extends({}, this.props, {
					options: _extends({}, options, {
						nodeTypes: ['Sitegeist.Taxonomy:Taxonomy']
					}),
					commit: this.handleCommit
				})),
				_react2.default.createElement(
					_reactUiComponents.Button,
					{
						className: _TaxonomyEditor2.default.button,
						onClick: this.handleOpenSecondaryScreen,
						isActive: Boolean(secondaryInspectorPortal)
					},
					_react2.default.createElement(_reactUiComponents.Icon, { className: _TaxonomyEditor2.default.icon, icon: 'sitemap' }),
					'Toggle Taxonomy Tree'
				),
				secondaryInspectorPortal ? (0, _reactDom.createPortal)(_react2.default.createElement(TaxonomyTreeSelect, {
					value: Array.isArray(value) ? value : [],
					identifier: identifier,
					options: options,
					onToggleTaxonomy: this.handleToggleTaxonomyInSecondaryInspector,
					onToggleTaxonomyBranch: this.handleToggleTaxonomyBranchInSecondaryInspector,
					onInitializeTaxonomyBranches: this.handleInitializeTaxonomyBranchesInSecondaryInspector,
					openBranches: openTaxonomyBranchesInSecondaryInspector || []
				}), secondaryInspectorPortal) : null
			);
		}
	}]);

	return TaxonomyEditor;
}(_react.PureComponent), _class2.propTypes = {
	value: _propTypes2.default.string,
	identifier: _propTypes2.default.string,
	renderSecondaryInspector: _propTypes2.default.func.isRequired,
	commit: _propTypes2.default.func.isRequired,
	options: _propTypes2.default.array,

	ReferencesEditor: _propTypes2.default.func.isRequired,
	TaxonomyTreeSelect: _propTypes2.default.func.isRequired
}, _temp2)) || _class);
exports.default = TaxonomyEditor;

/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('vendor')().ReactDOM;

/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(21);

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(8)(content, options);

if(content.locals) module.exports = content.locals;

if(false) {
	module.hot.accept("!!../node_modules/css-loader/index.js??ref--7-2!../node_modules/postcss-loader/lib/index.js??ref--7-3!./TaxonomyEditor.css", function() {
		var newContent = require("!!../node_modules/css-loader/index.js??ref--7-2!../node_modules/postcss-loader/lib/index.js??ref--7-3!./TaxonomyEditor.css");

		if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];

		var locals = (function(a, b) {
			var key, idx = 0;

			for(key in a) {
				if(!b || a[key] !== b[key]) return false;
				idx++;
			}

			for(key in b) idx--;

			return idx === 0;
		}(content.locals, newContent.locals));

		if(!locals) throw new Error('Aborting CSS HMR due to changed css-modules locals.');

		update(newContent);
	});

	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(7)(false);
// imports


// module
exports.push([module.i, ".TaxonomyEditor__button___2QdwU.TaxonomyEditor__button___2QdwU {\n    margin-top: 1em;\n    text-align: left;\n}\n\n.TaxonomyEditor__icon___2yFts.TaxonomyEditor__icon___2yFts {\n    margin-right: 1em;\n}", ""]);

// exports
exports.locals = {
	"button": "TaxonomyEditor__button___2QdwU",
	"icon": "TaxonomyEditor__icon___2yFts"
};

/***/ }),
/* 22 */
/***/ (function(module, exports) {


/**
 * When source maps are enabled, `style-loader` uses a link element with a data-uri to
 * embed the css on the page. This breaks all relative urls because now they are relative to a
 * bundle instead of the current page.
 *
 * One solution is to only use full urls, but that may be impossible.
 *
 * Instead, this function "fixes" the relative urls to be absolute according to the current page location.
 *
 * A rudimentary test suite is located at `test/fixUrls.js` and can be run via the `npm test` command.
 *
 */

module.exports = function (css) {
  // get current location
  var location = typeof window !== "undefined" && window.location;

  if (!location) {
    throw new Error("fixUrls requires window.location");
  }

	// blank or null?
	if (!css || typeof css !== "string") {
	  return css;
  }

  var baseUrl = location.protocol + "//" + location.host;
  var currentDir = baseUrl + location.pathname.replace(/\/[^\/]*$/, "/");

	// convert each url(...)
	/*
	This regular expression is just a way to recursively match brackets within
	a string.

	 /url\s*\(  = Match on the word "url" with any whitespace after it and then a parens
	   (  = Start a capturing group
	     (?:  = Start a non-capturing group
	         [^)(]  = Match anything that isn't a parentheses
	         |  = OR
	         \(  = Match a start parentheses
	             (?:  = Start another non-capturing groups
	                 [^)(]+  = Match anything that isn't a parentheses
	                 |  = OR
	                 \(  = Match a start parentheses
	                     [^)(]*  = Match anything that isn't a parentheses
	                 \)  = Match a end parentheses
	             )  = End Group
              *\) = Match anything and then a close parens
          )  = Close non-capturing group
          *  = Match anything
       )  = Close capturing group
	 \)  = Match a close parens

	 /gi  = Get all matches, not the first.  Be case insensitive.
	 */
	var fixedCss = css.replace(/url\s*\(((?:[^)(]|\((?:[^)(]+|\([^)(]*\))*\))*)\)/gi, function(fullMatch, origUrl) {
		// strip quotes (if they exist)
		var unquotedOrigUrl = origUrl
			.trim()
			.replace(/^"(.*)"$/, function(o, $1){ return $1; })
			.replace(/^'(.*)'$/, function(o, $1){ return $1; });

		// already a full url? no change
		if (/^(#|data:|http:\/\/|https:\/\/|file:\/\/\/|\s*$)/i.test(unquotedOrigUrl)) {
		  return fullMatch;
		}

		// convert the url to a full url
		var newUrl;

		if (unquotedOrigUrl.indexOf("//") === 0) {
		  	//TODO: should we add protocol?
			newUrl = unquotedOrigUrl;
		} else if (unquotedOrigUrl.indexOf("/") === 0) {
			// path should be relative to the base url
			newUrl = baseUrl + unquotedOrigUrl; // already starts with '/'
		} else {
			// path should be relative to current directory
			newUrl = currentDir + unquotedOrigUrl.replace(/^\.\//, ""); // Strip leading './'
		}

		// send back the fixed url(...)
		return "url(" + JSON.stringify(newUrl) + ")";
	});

	// send back the fixed css
	return fixedCss;
};


/***/ }),
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
	value: true
});
exports.default = undefined;

var _slicedToArray = function () { function sliceIterator(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"]) _i["return"](); } finally { if (_d) throw _e; } } return _arr; } return function (arr, i) { if (Array.isArray(arr)) { return arr; } else if (Symbol.iterator in Object(arr)) { return sliceIterator(arr, i); } else { throw new TypeError("Invalid attempt to destructure non-iterable instance"); } }; }();

var _extends = Object.assign || function (target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i]; for (var key in source) { if (Object.prototype.hasOwnProperty.call(source, key)) { target[key] = source[key]; } } } return target; };

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

var _dec, _dec2, _class, _class2, _temp2;

var _react = __webpack_require__(3);

var _react2 = _interopRequireDefault(_react);

var _propTypes = __webpack_require__(4);

var _propTypes2 = _interopRequireDefault(_propTypes);

var _reactRedux = __webpack_require__(24);

var _plowJs = __webpack_require__(25);

var _classnames = __webpack_require__(26);

var _classnames2 = _interopRequireDefault(_classnames);

var _neosUiDecorators = __webpack_require__(5);

var _neosUiBackendConnector = __webpack_require__(27);

var _neosUiReduxStore = __webpack_require__(28);

var _reactUiComponents = __webpack_require__(6);

var _TaxonomyTreeSelect = __webpack_require__(29);

var _TaxonomyTreeSelect2 = _interopRequireDefault(_TaxonomyTreeSelect);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

function _asyncToGenerator(fn) { return function () { var gen = fn.apply(this, arguments); return new Promise(function (resolve, reject) { function step(key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { return Promise.resolve(value).then(function (value) { step("next", value); }, function (err) { step("throw", err); }); } } return step("next"); }); }; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var TaxonomyTreeSelect = (_dec = (0, _reactRedux.connect)(function (state, _ref) {
	var identifier = _ref.identifier;

	var contextForNodeLinking = _neosUiReduxStore.selectors.UI.NodeLinking.contextForNodeLinking(state);
	var unsanitizedSourceValue = (0, _plowJs.$get)(['properties', identifier], _neosUiReduxStore.selectors.CR.Nodes.focusedSelector(state));
	var sourceValue = Array.isArray(unsanitizedSourceValue) ? unsanitizedSourceValue : [];

	return { contextForNodeLinking: contextForNodeLinking, sourceValue: sourceValue };
}), _dec2 = (0, _neosUiDecorators.neos)(function (globalRegistry) {
	var nodeTypesRegistry = globalRegistry.get('@neos-project/neos-ui-contentrepository');

	return { nodeTypesRegistry: nodeTypesRegistry };
}), _dec(_class = _dec2(_class = (_temp2 = _class2 = function (_PureComponent) {
	_inherits(TaxonomyTreeSelect, _PureComponent);

	function TaxonomyTreeSelect() {
		var _ref2;

		var _temp, _this, _ret;

		_classCallCheck(this, TaxonomyTreeSelect);

		for (var _len = arguments.length, args = Array(_len), _key = 0; _key < _len; _key++) {
			args[_key] = arguments[_key];
		}

		return _ret = (_temp = (_this = _possibleConstructorReturn(this, (_ref2 = TaxonomyTreeSelect.__proto__ || Object.getPrototypeOf(TaxonomyTreeSelect)).call.apply(_ref2, [this].concat(args))), _this), _this.state = {
			tree: []
		}, _this.renderNodeLabel = function (node) {
			var _this$props = _this.props,
			    _this$props$value = _this$props.value,
			    value = _this$props$value === undefined ? [] : _this$props$value,
			    onToggleTaxonomy = _this$props.onToggleTaxonomy,
			    nodeTypesRegistry = _this$props.nodeTypesRegistry;

			var nodeType = nodeTypesRegistry.getNodeType(node.nodeType);

			if (nodeTypesRegistry.isOfType(node.nodeType, 'Sitegeist.Taxonomy:Taxonomy')) {
				return _react2.default.createElement(
					_react.Fragment,
					null,
					_react2.default.createElement('input', {
						className: _TaxonomyTreeSelect2.default.checkbox,
						type: 'checkbox',
						id: 'taxonomy-treeselect-node-label-' + node.identifier,
						checked: value.includes(node.identifier),
						onClick: function onClick(e) {
							return e.stopPropagation();
						}
					}),
					_react2.default.createElement(
						'label',
						{
							className: _TaxonomyTreeSelect2.default.label,
							htmlFor: 'taxonomy-treeselect-node-label-' + node.identifier,
							onClick: function onClick(e) {
								e.stopPropagation();
								e.preventDefault();
								onToggleTaxonomy(node.identifier);
							},
							title: node.description
						},
						_react2.default.createElement(_reactUiComponents.Icon, { className: _TaxonomyTreeSelect2.default.icon, icon: (0, _plowJs.$get)('ui.icon', nodeType) }),
						_react2.default.createElement(
							'span',
							{ className: _TaxonomyTreeSelect2.default.title },
							node.title
						),
						_react2.default.createElement(
							'small',
							{ className: _TaxonomyTreeSelect2.default.nodePath },
							node.path
						)
					)
				);
			}

			return _react2.default.createElement(
				'div',
				{ className: _TaxonomyTreeSelect2.default.label, title: node.description },
				_react2.default.createElement(
					'span',
					{ className: _TaxonomyTreeSelect2.default.title },
					node.title
				),
				_react2.default.createElement(
					'small',
					{ className: _TaxonomyTreeSelect2.default.nodePath },
					node.path
				)
			);
		}, _this.renderTreeRecursively = function (tree) {
			var depth = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
			var _this$props2 = _this.props,
			    onToggleTaxonomyBranch = _this$props2.onToggleTaxonomyBranch,
			    openBranches = _this$props2.openBranches,
			    value = _this$props2.value,
			    sourceValue = _this$props2.sourceValue;


			return _react2.default.createElement(
				'ul',
				{ className: _TaxonomyTreeSelect2.default.list },
				tree.map(function (node) {
					return _react2.default.createElement(
						'li',
						{ key: node.identifier, className: _TaxonomyTreeSelect2.default.item },
						_react2.default.createElement('span', {
							className: (0, _classnames2.default)(_defineProperty({}, _TaxonomyTreeSelect2.default.isDirty, sourceValue.includes(node.identifier) ? !value.includes(node.identifier) : value.includes(node.identifier)))
						}),
						node.children.length ? _react2.default.createElement(
							'details',
							{
								className: _TaxonomyTreeSelect2.default.details,
								open: openBranches.includes(node.identifier) ? 'open' : null
							},
							_react2.default.createElement(
								'summary',
								{
									className: _TaxonomyTreeSelect2.default.summary,
									style: { paddingLeft: depth * 18 + 'px' },
									onClick: function onClick(e) {
										e.preventDefault();
										e.stopPropagation();
										onToggleTaxonomyBranch(node.identifier);
									}
								},
								_this.renderNodeLabel(node)
							),
							node.children && _this.renderTreeRecursively(node.children, depth + 1)
						) : _react2.default.createElement(
							'div',
							{
								className: _TaxonomyTreeSelect2.default.summary,
								style: { paddingLeft: depth * 18 + 'px' },
								onClick: function onClick(e) {
									return e.stopPropagation();
								}
							},
							_this.renderNodeLabel(node)
						)
					);
				})
			);
		}, _temp), _possibleConstructorReturn(_this, _ret);
	}

	_createClass(TaxonomyTreeSelect, [{
		key: 'componentDidMount',
		value: function () {
			var _ref3 = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee() {
				var _this2 = this;

				return regeneratorRuntime.wrap(function _callee$(_context) {
					while (1) {
						switch (_context.prev = _context.next) {
							case 0:
								_context.t0 = this;
								_context.next = 3;
								return this.tree;

							case 3:
								_context.t1 = _context.sent;
								_context.t2 = {
									tree: _context.t1
								};

								_context.t3 = function () {
									var onInitializeTaxonomyBranches = _this2.props.onInitializeTaxonomyBranches;


									onInitializeTaxonomyBranches(_this2.state.tree.children.map(function (node) {
										return node.identifier;
									}));
								};

								_context.t0.setState.call(_context.t0, _context.t2, _context.t3);

							case 7:
							case 'end':
								return _context.stop();
						}
					}
				}, _callee, this);
			}));

			function componentDidMount() {
				return _ref3.apply(this, arguments);
			}

			return componentDidMount;
		}()
	}, {
		key: 'render',
		value: function render() {
			var tree = this.state.tree;


			return _react2.default.createElement(
				'div',
				{ className: _TaxonomyTreeSelect2.default.taxonomyTreeSelect },
				_react2.default.createElement(
					'h2',
					{ className: _TaxonomyTreeSelect2.default.headline },
					'Taxonomies'
				),
				tree && tree.children && this.renderTreeRecursively(tree.children)
			);
		}
	}, {
		key: 'searchNodesQuery',
		get: function get() {
			var _props = this.props,
			    contextForNodeLinking = _props.contextForNodeLinking,
			    options = _props.options;


			return _extends({}, contextForNodeLinking, {
				nodeTypes: options.nodeTypes,
				contextNode: options.startingPoint
			});
		}
	}, {
		key: 'tree',
		get: function get() {
			var _props2 = this.props,
			    contextForNodeLinking = _props2.contextForNodeLinking,
			    options = _props2.options;

			var _contextForNodeLinkin = contextForNodeLinking.contextNode.split('@'),
			    _contextForNodeLinkin2 = _slicedToArray(_contextForNodeLinkin, 2),
			    contextString = _contextForNodeLinkin2[1];

			var startingPointWithContext = options.startingPoint + '@' + contextString;

			return _neosUiBackendConnector.fetchWithErrorHandling.withCsrfToken(function (csrfToken) {
				return {
					url: '/taxonomy/secondary-inspector/tree?contextNode=' + startingPointWithContext,
					method: 'GET',
					credentials: 'include',
					headers: {
						'X-Flow-Csrftoken': csrfToken,
						'Content-Type': 'application/json'
					}
				};
			}).then(function (res) {
				return res.json();
			});
		}
	}]);

	return TaxonomyTreeSelect;
}(_react.PureComponent), _class2.propTypes = {
	nodeTypesRegistry: _propTypes2.default.object.isRequired,
	contextForNodeLinking: _propTypes2.default.object.isRequired,
	options: _propTypes2.default.object.isRequired,
	onToggleTaxonomy: _propTypes2.default.func.isRequired,
	onToggleTaxonomyBranch: _propTypes2.default.func.isRequired,
	onInitializeTaxonomyBranches: _propTypes2.default.func.isRequired,
	openBranches: _propTypes2.default.array,
	value: _propTypes2.default.array,
	sourceValue: _propTypes2.default.array
}, _temp2)) || _class) || _class);
exports.default = TaxonomyTreeSelect;

/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('vendor')().reactRedux;

/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('vendor')().plow;

/***/ }),
/* 26 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('vendor')().classnames;

/***/ }),
/* 27 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.fetchWithErrorHandling = undefined;

var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = (0, _readFromConsumerApi2.default)('NeosProjectPackages')().NeosUiBackendConnectorDefault;
var fetchWithErrorHandling = (0, _readFromConsumerApi2.default)('NeosProjectPackages')().NeosUiBackendConnector.fetchWithErrorHandling;
exports.fetchWithErrorHandling = fetchWithErrorHandling;

/***/ }),
/* 28 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var _readFromConsumerApi = __webpack_require__(0);

var _readFromConsumerApi2 = _interopRequireDefault(_readFromConsumerApi);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

module.exports = (0, _readFromConsumerApi2.default)('NeosProjectPackages')().NeosUiReduxStore;

/***/ }),
/* 29 */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(30);

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(8)(content, options);

if(content.locals) module.exports = content.locals;

if(false) {
	module.hot.accept("!!../node_modules/css-loader/index.js??ref--7-2!../node_modules/postcss-loader/lib/index.js??ref--7-3!./TaxonomyTreeSelect.css", function() {
		var newContent = require("!!../node_modules/css-loader/index.js??ref--7-2!../node_modules/postcss-loader/lib/index.js??ref--7-3!./TaxonomyTreeSelect.css");

		if(typeof newContent === 'string') newContent = [[module.id, newContent, '']];

		var locals = (function(a, b) {
			var key, idx = 0;

			for(key in a) {
				if(!b || a[key] !== b[key]) return false;
				idx++;
			}

			for(key in b) idx--;

			return idx === 0;
		}(content.locals, newContent.locals));

		if(!locals) throw new Error('Aborting CSS HMR due to changed css-modules locals.');

		update(newContent);
	});

	module.hot.dispose(function() { update(); });
}

/***/ }),
/* 30 */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(7)(false);
// imports


// module
exports.push([module.i, ".TaxonomyTreeSelect__taxonomyTreeSelect___2RpWx {\n    padding: 1em;\n    overflow: auto;\n    max-height: calc(100vh - 82px);\n}\n\n.TaxonomyTreeSelect__list___3w-ZX {\n    list-style-type: none;\n    margin: 0;\n    padding: 0 0 0 15px;\n}\n\n.TaxonomyTreeSelect__item___teI03 {\n    position: relative;\n}\n\n.TaxonomyTreeSelect__details___15JXk {\n    margin-left: -15px;\n}\n\n.TaxonomyTreeSelect__isDirty___z5kIO {\n    position: absolute;\n    top: 0;\n    left: -25px;\n    height: 24px;\n    border-left: 2px solid #ff8700;\n}\n\n.TaxonomyTreeSelect__label___i897j {\n    display: -ms-inline-flexbox;\n    display: inline-flex;\n    -ms-flex-align: center;\n        align-items: center;\n    min-height: 24px;\n}\n\n.TaxonomyTreeSelect__label___i897j .TaxonomyTreeSelect__icon___32hds {\n        display: inline-block;\n        width: 2em;\n        text-align: center;\n    }\n\n.TaxonomyTreeSelect__label___i897j .TaxonomyTreeSelect__title___3x-X3 {\n        display: inline-block;\n        margin-right: 1em;\n    }\n\n.TaxonomyTreeSelect__checkbox___1ANen:checked + .TaxonomyTreeSelect__label___i897j .TaxonomyTreeSelect__title___3x-X3 {\n            color: #00ADEE;\n        }\n\n.TaxonomyTreeSelect__label___i897j .TaxonomyTreeSelect__nodePath___GlOGF {\n        display: inline-block;\n        opacity: .5;\n    }\n\nlabel.TaxonomyTreeSelect__label___i897j {\n    cursor: pointer;\n}\n\n.TaxonomyTreeSelect__checkbox___1ANen {\n    display: none;\n}", ""]);

// exports
exports.locals = {
	"taxonomyTreeSelect": "TaxonomyTreeSelect__taxonomyTreeSelect___2RpWx",
	"list": "TaxonomyTreeSelect__list___3w-ZX",
	"item": "TaxonomyTreeSelect__item___teI03",
	"details": "TaxonomyTreeSelect__details___15JXk",
	"isDirty": "TaxonomyTreeSelect__isDirty___z5kIO",
	"label": "TaxonomyTreeSelect__label___i897j",
	"icon": "TaxonomyTreeSelect__icon___32hds",
	"title": "TaxonomyTreeSelect__title___3x-X3",
	"checkbox": "TaxonomyTreeSelect__checkbox___1ANen",
	"nodePath": "TaxonomyTreeSelect__nodePath___GlOGF"
};

/***/ })
/******/ ]);
//# sourceMappingURL=Plugin.js.map