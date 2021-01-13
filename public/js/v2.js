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
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
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
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./resources/v2/js/v2.js":
/*!*******************************!*\
  !*** ./resources/v2/js/v2.js ***!
  \*******************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

// Posts Class
// This class is used to handle the position of the highlighting in keyboard shortcuts
var Posts = /*#__PURE__*/function () {
  function Posts(number, index) {
    _classCallCheck(this, Posts);

    this.number = number;
    this.index = index;
  }

  _createClass(Posts, [{
    key: "NextPost",
    value: function NextPost() {
      if (this.index < this.number - 1) {
        this.index++;
      }
    }
  }, {
    key: "PreviousPost",
    value: function PreviousPost() {
      if (this.index > 0) {
        this.index--;
      }
    }
  }, {
    key: "GetIndex",
    value: function GetIndex() {
      return this.index;
    }
  }, {
    key: "ResetIndex",
    value: function ResetIndex() {
      this.index = 0;
    }
  }, {
    key: "GetNumber",
    value: function GetNumber() {
      return this.number;
    }
  }, {
    key: "markPostAsRead",
    value: function markPostAsRead() {
      this.number = this.number - 1;
    }
  }]);

  return Posts;
}();

window.addEventListener('DOMContentLoaded', function (event) {
  IR_posts = new Posts(numberOfPosts, 0);
  var keyboard_navigation = false; // When a post is marked as read. Reduce the count of the posts.

  Livewire.on('markAsRead', function () {
    IR_posts.markPostAsRead();
  }); // Function to update the position of the highlight

  function updateHighlightPosition() {
    if (keyboard_navigation == false) {
      IR_posts.ResetIndex();
      keyboard_navigation = true;
    }

    Livewire.emit('postHighlighted', IR_posts.GetIndex());
    document.querySelector('#post-' + IR_posts.GetIndex()).scrollIntoView({
      behavior: "smooth",
      block: "center",
      inline: "nearest"
    });
  } // Keyboard shortcuts


  window.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      Livewire.emit('exitPost');
    }

    if (e.key == 'j' || e.key == 'J') {
      IR_posts.NextPost();
      updateHighlightPosition();
    }

    if (e.key == 'k' || e.key == 'K') {
      IR_posts.PreviousPost();
      updateHighlightPosition();
    }

    console.log(e.key);
  });
});

/***/ }),

/***/ 2:
/*!*************************************!*\
  !*** multi ./resources/v2/js/v2.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/mustaphahamoui/sites/infraread/resources/v2/js/v2.js */"./resources/v2/js/v2.js");


/***/ })

/******/ });