/* ===================================================
 * bootstrap-transition.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#transitions
 * ===================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */
!function($){
    $(function () {
    "use strict"; // jshint ;_;
    /* CSS TRANSITION SUPPORT (http://www.modernizr.com/)
     * ======================================================= */
    $.support.transition = (function () {
      var transitionEnd = (function () {
        var el = document.createElement('bootstrap')
          , transEndEventNames = {
               'WebkitTransition' : 'webkitTransitionEnd'
            ,  'MozTransition'    : 'transitionend'
            ,  'OTransition'      : 'oTransitionEnd'
            ,  'msTransition'     : 'MSTransitionEnd'
            ,  'transition'       : 'transitionend'
            }
          , name
        for (name in transEndEventNames){
          if (el.style[name] !== undefined) {
            return transEndEventNames[name]
          }
        }
      }())
      return transitionEnd && {
        end: transitionEnd
      }
    })()
  })
}(window.jQuery);/* ==========================================================
 * bootstrap-alert.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#alerts
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */
function isMobile(userAgent){
    userAgent = userAgent || navigator.userAgent;
    return (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(userAgent));
}
!function($){
  "use strict"; // jshint ;_;

 /* ALERT CLASS DEFINITION
  * ====================== */
  var dismiss = '[data-dismiss="alert"]'
    , Alert = function (el) {
        $(el).on('click', dismiss, this.close)
      }
  Alert.prototype.close = function (e) {
    var $this = $(this)
      , selector = $this.attr('data-target')
      , $parent
    if (!selector) {
      selector = $this.attr('href')
      selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
    }
    $parent = $(selector)
    e && e.preventDefault()
    $parent.length || ($parent = $this.hasClass('alert') ? $this : $this.parent())
    $parent.trigger(e = $.Event('close'))
    if (e.isDefaultPrevented()) return
    $parent.removeClass('in')
    function removeElement() {
      $parent
        .trigger('closed')
        .remove()
    }
    $.support.transition && $parent.hasClass('fade') ?
      $parent.on($.support.transition.end, removeElement) :
      removeElement()
  }
  $.fn.alert = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('alert')
      if (!data) $this.data('alert', (data = new Alert(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }
  $.fn.alert.Constructor = Alert
  $(function () {
    $('body').on('click.alert.data-api', dismiss, Alert.prototype.close)
  })
}(window.jQuery);/* ============================================================
 * bootstrap-button.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#buttons
 * ============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */
!function ($) {
  "use strict"; // jshint ;_;

    /* BUTTON PUBLIC CLASS DEFINITION  * ============================== */
  var Button = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.button.defaults, options)
  }
  Button.prototype.setState = function (state) {
    var d = 'disabled'
      , $el = this.$element
      , data = $el.data()
      , val = $el.is('input') ? 'val' : 'html'
    state = state + 'Text'
    data.resetText || $el.data('resetText', $el[val]())
    $el[val](data[state] || this.options[state])
    // push to event loop to allow forms to submit
    setTimeout(function () {
      state == 'loadingText' ?
        $el.addClass(d).attr(d, d) :
        $el.removeClass(d).removeAttr(d)
    }, 0)
  }
  Button.prototype.toggle = function () {
    var $parent = this.$element.parent('[data-toggle="buttons-radio"]')
    $parent && $parent
      .find('.active')
      .removeClass('active')
    this.$element.toggleClass('active')
  }

 /* BUTTON PLUGIN DEFINITION
  * ======================== */
  $.fn.button = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('button')
        , options = typeof option == 'object' && option
      if (!data) $this.data('button', (data = new Button(this, options)))
      if (option == 'toggle') data.toggle()
      else if (option) data.setState(option)
    })
  }
  $.fn.button.defaults = {
    loadingText: 'loading...'
  }
  $.fn.button.Constructor = Button

 /* BUTTON DATA-API
  * =============== */
  $(function () {
    $('body').on('click.button.data-api', '[data-toggle^=button]', function ( e ) {
      var $btn = $(e.target)
      if (!$btn.hasClass('btn')) $btn = $btn.closest('.btn')
      $btn.button('toggle')
    })
  })
}(window.jQuery);/* ==========================================================
 * bootstrap-carousel.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#carousel
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */

!function ($) {
  "use strict"; // jshint ;_;
  var Carousel = function (element, options) {
    this.$element = $(element)
    this.options = options
    this.options.slide && this.slide(this.options.slide)
    this.options.pause == 'hover' && this.$element
      .on('mouseenter', $.proxy(this.pause, this))
      .on('mouseleave', $.proxy(this.cycle, this))
  }
  Carousel.prototype = {
    cycle: function (e) {
      if (!e) this.paused = false
      this.options.interval
        && !this.paused
        && (this.interval = setInterval($.proxy(this.next, this), this.options.interval))
      return this
    }
  , to: function (pos) {
      var $active = this.$element.find('.active')
        , children = $active.parent().children()
        , activePos = children.index($active)
        , that = this
      if (pos > (children.length - 1) || pos < 0) return
      if (this.sliding) {
        return this.$element.one('slid', function () {
          that.to(pos)
        })
      }
      if (activePos == pos) {
        return this.pause().cycle()
      }
      return this.slide(pos > activePos ? 'next' : 'prev', $(children[pos]))
    }
  , pause: function (e) {
      if (!e) this.paused = true
      clearInterval(this.interval)
      this.interval = null
      return this
    }
  , next: function () {
      if (this.sliding) return
      return this.slide('next')
    }
  , prev: function () {
      if (this.sliding) return
      return this.slide('prev')
    }
  , slide: function (type, next) {
      var $active = this.$element.find('.active')
        , $next = next || $active[type]()
        , isCycling = this.interval
        , direction = type == 'next' ? 'left' : 'right'
        , fallback  = type == 'next' ? 'first' : 'last'
        , that = this
        , e = $.Event('slide')
      this.sliding = true
      isCycling && this.pause()
      $next = $next.length ? $next : this.$element.find('.item')[fallback]()
      if ($next.hasClass('active')) return
      if ($.support.transition && this.$element.hasClass('slide')) {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $next.addClass(type)
        $next[0].offsetWidth // force reflow
        $active.addClass(direction)
        $next.addClass(direction)
        this.$element.one($.support.transition.end, function () {
          $next.removeClass([type, direction].join(' ')).addClass('active')
          $active.removeClass(['active', direction].join(' '))
          that.sliding = false
          setTimeout(function () { that.$element.trigger('slid') }, 0)
        })
      } else {
        this.$element.trigger(e)
        if (e.isDefaultPrevented()) return
        $active.removeClass('active')
        $next.addClass('active')
        this.sliding = false
        this.$element.trigger('slid')
      }
      isCycling && this.cycle()
      return this
    }
  }
  $.fn.carousel = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('carousel')
        , options = $.extend({}, $.fn.carousel.defaults, typeof option == 'object' && option)
      if (!data) $this.data('carousel', (data = new Carousel(this, options)))
      if (typeof option == 'number') data.to(option)
      else if (typeof option == 'string' || (option = options.slide)) data[option]()
      else if (options.interval) data.cycle()
    })
  }
  $.fn.carousel.defaults = {
    interval: 5000
  , pause: 'hover'
  }
  $.fn.carousel.Constructor = Carousel

  /* CAROUSEL DATA-API  * ================= */
  $(function () {
    $('body').on('click.carousel.data-api', '[data-slide]', function ( e ) {
      var $this = $(this), href
        , $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
        , options = !$target.data('modal') && $.extend({}, $target.data(), $this.data())
      $target.carousel(options)
      e.preventDefault()
    })
  })
}(window.jQuery);/* =============================================================
 * bootstrap-collapse.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#collapse
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */

!function ($) {
  "use strict"; // jshint ;_;

 /* COLLAPSE PUBLIC CLASS DEFINITION
  * ================================ */
  var Collapse = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.collapse.defaults, options)
    if (this.options.parent) {
      this.$parent = $(this.options.parent)
    }
    this.options.toggle && this.toggle()
  }
  Collapse.prototype = {
    constructor: Collapse
  , dimension: function () {
      var hasWidth = this.$element.hasClass('width')
      return hasWidth ? 'width' : 'height'
    }
  , show: function () {
      var dimension
        , scroll
        , actives
        , hasData
      if (this.transitioning) return
      dimension = this.dimension()
      scroll = $.camelCase(['scroll', dimension].join('-'))
      actives = this.$parent && this.$parent.find('> .accordion-group > .in')
      if (actives && actives.length) {
        hasData = actives.data('collapse')
        if (hasData && hasData.transitioning) return
        actives.collapse('hide')
        hasData || actives.data('collapse', null)
      }
      this.$element[dimension](0)
      this.transition('addClass', $.Event('show'), 'shown')
      this.$element[dimension](this.$element[0][scroll])
    }
  , hide: function () {
      var dimension
      if (this.transitioning) return
      dimension = this.dimension()
      this.reset(this.$element[dimension]())
      this.transition('removeClass', $.Event('hide'), 'hidden')
      this.$element[dimension](0)
    }
  , reset: function (size) {
      var dimension = this.dimension()
      this.$element
        .removeClass('collapse')
        [dimension](size || 'auto')
        [0].offsetWidth
      this.$element[size !== null ? 'addClass' : 'removeClass']('collapse')
      return this
    }
  , transition: function (method, startEvent, completeEvent) {
      var that = this
        , complete = function () {
            if (startEvent.type == 'show') that.reset()
            that.transitioning = 0
            that.$element.trigger(completeEvent)
          }
      this.$element.trigger(startEvent)
      if (startEvent.isDefaultPrevented()) return
      this.transitioning = 1
      this.$element[method]('in')
      $.support.transition && this.$element.hasClass('collapse') ?
        this.$element.one($.support.transition.end, complete) :
        complete()
    }
  , toggle: function () {
      this[this.$element.hasClass('in') ? 'hide' : 'show']()
    }
  }

 /* COLLAPSIBLE PLUGIN DEFINITION
  * ============================== */
  $.fn.collapse = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('collapse')
        , options = typeof option == 'object' && option
      if (!data) $this.data('collapse', (data = new Collapse(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }
  $.fn.collapse.defaults = {
    toggle: true
  }
  $.fn.collapse.Constructor = Collapse

 /* COLLAPSIBLE DATA-API
  * ==================== */
  $(function () {
    $('body').on('click.collapse.data-api', '[data-toggle=collapse]', function ( e ) {
      var $this = $(this), href
        , target = $this.attr('data-target')
          || e.preventDefault()
          || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '') //strip for ie7
        , option = $(target).data('collapse') ? 'toggle' : $this.data()
      $(target).collapse(option)
    })
  })
}(window.jQuery);/* ============================================================
 * bootstrap-dropdown.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#dropdowns
 * ============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */

!function ($) {
  "use strict"; // jshint ;_;

 /* DROPDOWN CLASS DEFINITION
  * ========================= */
  var toggle = '[data-toggle="dropdown"]'
    , Dropdown = function (element) {
        var $el = $(element).on('click.dropdown.data-api', this.toggle)
        $('html').on('click.dropdown.data-api', function () {
          $el.parent().removeClass('open')
        })
      }
  Dropdown.prototype = {
    constructor: Dropdown
  , toggle: function (e) {
      var $this = $(this)
        , $parent
        , selector
        , isActive
      if ($this.is('.disabled, :disabled')) return
      selector = $this.attr('data-target')
      if (!selector) {
        selector = $this.attr('href')
        selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
      }
      $parent = $this.parents('.btn-group');//$(selector)
      $parent.length || ($parent = $this.parent())
      isActive = $parent.hasClass('open')
      clearMenus()
      if (!isActive) $parent.toggleClass('open')
      return false
    }
  }
  function clearMenus() {
    $(toggle).parents('.btn-group').removeClass('open')
  }

  /* DROPDOWN PLUGIN DEFINITION
   * ========================== */
  $.fn.dropdown = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('dropdown')
      if (!data) $this.data('dropdown', (data = new Dropdown(this)))
      if (typeof option == 'string') data[option].call($this)
    })
  }
  $.fn.dropdown.Constructor = Dropdown

  /* APPLY TO STANDARD DROPDOWN ELEMENTS
   * =================================== */
  $(function () {
    $('html').on('click.dropdown.data-api', clearMenus)
    $('body')
      .on('click.dropdown', '.dropdown form', function (e) { e.stopPropagation() })
      .on('click.dropdown.data-api', toggle, Dropdown.prototype.toggle)
  })
}(window.jQuery);
/* =========================================================
 * bootstrap-modal.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#modals
 * =========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================= */
!function ($) {
    "use strict"; // jshint ;_;
    /* MODAL CLASS DEFINITION  * ====================== */
    var Modal = function (content, options) {
        this.options = options
        this.$element = $(content)
        .delegate('[data-dismiss="modal"]', 'click.dismiss.modal', $.proxy(this.hide, this))
        .delegate('[data-yes="modal"]', 'click', $.proxy(this.yes, this))
    }
    
    Modal.prototype = {
        constructor: Modal
        , toggle: function () {
            return this[!this.isShown ? 'show' : 'hide']()
        }
        , show: function () {
            var that = this
            , e = $.Event('show')    
            this.$element.trigger(e)    
            if (this.isShown || e.isDefaultPrevented()) return;    
            $('body').addClass('modal-open')    
            this.isShown = true    
            escape.call(this)
            backdrop.call(this, function () {
                var transition = $.support.transition && that.$element.hasClass('fade')
                if (!that.$element.parent().length) {
                    that.$element.appendTo(document.body) //don't move modals dom position
                }
        
                that.$element.show()    
                if (transition) {
                    that.$element[0].offsetWidth // force reflow
                }
                that.$element.addClass('in')
                transition ?
                    that.$element.one($.support.transition.end, function () { that.$element.trigger('shown') }) :
                    that.$element.trigger('shown')
                var scroll_top  = $(document).scrollTop();
                var scroll_left = $(document).scrollLeft();
                var _height = $('body').height();
                var _top1   = that.$element.offset().top;
                var _top2   = scroll_top+_top1 > _height ? _top1 : scroll_top+_top1;
                var _self_h = that.$element.height();
                // alert(_self_h+':'+_height);
                var _top3   = (_height - _self_h)/3 + scroll_top;
                // console.log('_height:',_height, '_self_h:',_self_h,'scroll_top:',scroll_top, '_top3:',_top3)
                    _top3   = _top3 < 0 ? scroll_top : _top3;
                var _left = ($('body').width() - that.$element.width())/2;
                    _left = _left + scroll_left;
                that.$element.css({left:_left, top:_top3});
                if(isMobile()){
                  that.$element.css({left:0, right:0});
                }
            })
        }
        , hide: function (e) {
            e && e.preventDefault()    
            var that = this    
            e = $.Event('hide')    
            this.$element.trigger(e)    
            if (!this.isShown || e.isDefaultPrevented()) return    
            this.isShown = false    
            $('body').removeClass('modal-open')    
            escape.call(this)    
            this.$element.removeClass('in')    
            $.support.transition && this.$element.hasClass('fade') ?
            hideWithTransition.call(this) :
            hideModal.call(this)
        }
        , yes: function (e) {
          var opts = this.options;
          // console.log(opts);
          this.hide();
          if('function' == typeof(opts.fcbYes)){
            opts.fcbYes();
          }
        }
    }

    /* MODAL PRIVATE METHODS  * ===================== */
    function hideWithTransition() {
        var that = this
        , timeout = setTimeout(function () {
            that.$element.off($.support.transition.end)
            hideModal.call(that)
            }, 500)
    
        this.$element.one($.support.transition.end, function () {
        clearTimeout(timeout)
        hideModal.call(that)
        })
    }
    
    function hideModal(that) {
        this.$element.hide().trigger('hidden');
        backdrop.call(this);
        // alert
        if(this.options.remove){
          this.$element.remove();
        }
    }
    
    function backdrop(callback) {
        var that = this
        , animate = this.$element.hasClass('fade') ? 'fade' : ''
    
        if(this.isShown && this.options.backdrop) {
          var doAnimate = $.support.transition && animate
      
          this.$backdrop = $('<div class="modal-backdrop ' + animate + '" />')
              .appendTo(document.body)
      
          if (this.options.backdrop != 'static') {
              if(this.options.backdrop_click)this.$backdrop.click($.proxy(this.hide, this))
          }
      
          if (doAnimate) this.$backdrop[0].offsetWidth // force reflow
      
          this.$backdrop.addClass('in')
      
          doAnimate ?
              this.$backdrop.one($.support.transition.end, callback) :
              callback()
        }else if (!this.isShown && this.$backdrop) {
          this.$backdrop.removeClass('in')
      
          $.support.transition && this.$element.hasClass('fade')?
              this.$backdrop.one($.support.transition.end, $.proxy(removeBackdrop, this)) :
              removeBackdrop.call(this)
        }else if (callback) {
          callback()
        }
    }
    
    function removeBackdrop() {
        this.$backdrop.remove()
        this.$backdrop = null
    }
    
    function escape() {
        var that = this
        if (this.isShown && this.options.keyboard) {
          $(document).on('keyup.dismiss.modal', function ( e ) {
              e.which == 27 && that.hide()
          })
        } else if (!this.isShown) {
          $(document).off('keyup.dismiss.modal')
        }
    }
    /* MODAL PLUGIN DEFINITION  * ======================= */
    $.fn.modal = function (option) {
        return this.each(function () {
            var $this = $(this)
                , data = $this.data('modal')
                , options = $.extend({}, $.fn.modal.defaults, $this.data(), typeof option == 'object' && option)
            if(!data) $this.data('modal', (data = new Modal(this, options)));
            // console.log(data);
            if (typeof option == 'string') data[option]()
            else if (options.show) data.show();
            if('screen' == options.width){
              $this.css({width:'auto',height:'auto',top:8,bottom:8,left:8,right:8,position:'fixed'});
            }else if('fit' == options.width){
              $this.css({width:'auto',height:'auto',top:'20%',bottom:'20%',left:'20%',right:'20%',position:'fixed'});
            }else{
              if(options.drag) $this.drag();
              if('number' == typeof(options.width))$this.css({width:options.width});
            }
        })
    }
    $.fn.modal.defaults = {
          backdrop: true
        , backdrop_click: true  //+++++++++
        , keyboard: true
        , show:     true
        , drag:     true        //+++++++++
        , remove:   false
        // , width:    560
    }
    $.fn.modal.Constructor = Modal
    /* MODAL DATA-API  * ============== */
    $(function (){
        $('body').on('click.modal.data-api', '[data-toggle="modal"]', function(e) {
            var $this = $(this), href
                , $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
                , option = $target.data('modal') ? 'toggle' : $.extend({}, $target.data(), $this.data())
        
            e.preventDefault();
            $target.modal(option);
        })
    })
}(window.jQuery);/* ===========================================================
 * bootstrap-tooltip.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#tooltips
 * Inspired by the original jQuery.tipsy by Jason Frame
 * ===========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */

!function ($) {
  "use strict"; // jshint ;_;
 /* TOOLTIP PUBLIC CLASS DEFINITION
  * =============================== */
  var Tooltip = function (element, options) {
    this.init('tooltip', element, options)
  }
  Tooltip.prototype = {
    constructor: Tooltip
  , init: function (type, element, options) {
      var eventIn
        , eventOut
      this.type = type
      this.$element = $(element)
      this.options = this.getOptions(options)
      this.enabled = true
      if (this.options.trigger != 'manual') {
        eventIn  = this.options.trigger == 'hover' ? 'mouseenter' : 'focus'
        eventOut = this.options.trigger == 'hover' ? 'mouseleave' : 'blur'
        this.$element.on(eventIn, this.options.selector, $.proxy(this.enter, this))
        this.$element.on(eventOut, this.options.selector, $.proxy(this.leave, this))
      }
      this.options.selector ?
        (this._options = $.extend({}, this.options, { trigger: 'manual', selector: '' })) :
        this.fixTitle()
    }
  , getOptions: function (options) {
      options = $.extend({}, $.fn[this.type].defaults, options, this.$element.data())
      if (options.delay && typeof options.delay == 'number') {
        options.delay = {
          show: options.delay
        , hide: options.delay
        }
      }
      return options
    }
  , enter: function (e) {
      var self = $(e.currentTarget)[this.type](this._options).data(this.type)
      if (!self.options.delay || !self.options.delay.show) return self.show()
      clearTimeout(this.timeout)
      self.hoverState = 'in'
      this.timeout = setTimeout(function() {
        if (self.hoverState == 'in') self.show()
      }, self.options.delay.show)
    }
  , leave: function (e) {
      var self = $(e.currentTarget)[this.type](this._options).data(this.type)
      if (this.timeout) clearTimeout(this.timeout)
      if (!self.options.delay || !self.options.delay.hide) return self.hide()
      self.hoverState = 'out'
      this.timeout = setTimeout(function() {
        if (self.hoverState == 'out') self.hide()
      }, self.options.delay.hide)
    }
  , show: function () {
      var $tip
        , inside
        , pos
        , actualWidth
        , actualHeight
        , placement
        , tp
      if (this.hasContent() && this.enabled) {
        $tip = this.tip()
        this.setContent()
        if (this.options.animation) {
          $tip.addClass('fade')
        }
        placement = typeof this.options.placement == 'function' ?
          this.options.placement.call(this, $tip[0], this.$element[0]) :
          this.options.placement
        inside = /in/.test(placement)
        $tip
          .remove()
          .css({ top: 0, left: 0, display: 'block' })
          .appendTo(inside ? this.$element : document.body)
        pos = this.getPosition(inside)
        actualWidth = $tip[0].offsetWidth
        actualHeight = $tip[0].offsetHeight
        switch (inside ? placement.split(' ')[1] : placement) {
          case 'bottom':
            tp = {top: pos.top + pos.height, left: pos.left + pos.width / 2 - actualWidth / 2}
            break
          case 'top':
            tp = {top: pos.top - actualHeight, left: pos.left + pos.width / 2 - actualWidth / 2}
            break
          case 'left':
            tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth}
            break
          case 'right':
            tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width}
            break
        }
        $tip
          .css(tp)
          .addClass(placement)
          .addClass('in')
      }
    }
  , isHTML: function(text) {
      // html string detection logic adapted from jQuery
      return typeof text != 'string'
        || ( text.charAt(0) === "<"
          && text.charAt( text.length - 1 ) === ">"
          && text.length >= 3
        ) || /^(?:[^<]*<[\w\W]+>[^>]*$)/.exec(text)
    }
  , setContent: function () {
      var $tip = this.tip()
        , title = this.getTitle()
      $tip.find('.tooltip-inner')[this.isHTML(title) ? 'html' : 'text'](title)
      $tip.removeClass('fade in top bottom left right')
    }
  , hide: function () {
      var that = this
        , $tip = this.tip()
      $tip.removeClass('in')
      function removeWithAnimation() {
        var timeout = setTimeout(function () {
          $tip.off($.support.transition.end).remove()
        }, 500)
        $tip.one($.support.transition.end, function () {
          clearTimeout(timeout)
          $tip.remove()
        })
      }
      $.support.transition && this.$tip.hasClass('fade') ?
        removeWithAnimation() :
        $tip.remove()
    }
  , fixTitle: function () {
      var $e = this.$element
      if ($e.attr('title') || typeof($e.attr('data-original-title')) != 'string') {
        $e.attr('data-original-title', $e.attr('title') || '').removeAttr('title')
      }
    }
  , hasContent: function () {
      return this.getTitle()
    }
  , getPosition: function (inside) {
      return $.extend({}, (inside ? {top: 0, left: 0} : this.$element.offset()), {
        width: this.$element[0].offsetWidth
      , height: this.$element[0].offsetHeight
      })
    }
  , getTitle: function () {
      var title
        , $e = this.$element
        , o = this.options
      title = $e.attr('data-original-title')
        || (typeof o.title == 'function' ? o.title.call($e[0]) :  o.title)
      return title
    }
  , tip: function () {
      return this.$tip = this.$tip || $(this.options.template)
    }
  , validate: function () {
      if (!this.$element[0].parentNode) {
        this.hide()
        this.$element = null
        this.options = null
      }
    }
  , enable: function () {
      this.enabled = true
    }
  , disable: function () {
      this.enabled = false
    }
  , toggleEnabled: function () {
      this.enabled = !this.enabled
    }
  , toggle: function () {
      this[this.tip().hasClass('in') ? 'hide' : 'show']()
    }
  }

 /* TOOLTIP PLUGIN DEFINITION
  * ========================= */
  $.fn.tooltip = function ( option ) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('tooltip')
        , options = typeof option == 'object' && option
      if (!data) $this.data('tooltip', (data = new Tooltip(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }
  $.fn.tooltip.Constructor = Tooltip
  $.fn.tooltip.defaults = {
    animation: true
  , placement: 'top'
  , selector: false
  , template: '<div class="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
  , trigger: 'hover'
  , title: ''
  , delay: 0
  }
}(window.jQuery);
/* ===========================================================
 * bootstrap-popover.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#popovers
 * ===========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =========================================================== */

!function ($) {
  "use strict"; // jshint ;_;
 /* POPOVER PUBLIC CLASS DEFINITION
  * =============================== */
  var Popover = function ( element, options ) {
    this.init('popover', element, options)
  }
  /* NOTE: POPOVER EXTENDS BOOTSTRAP-TOOLTIP.js
     ========================================== */
  Popover.prototype = $.extend({}, $.fn.tooltip.Constructor.prototype, {
    constructor: Popover
  , setContent: function () {
      var $tip = this.tip()
        , title = this.getTitle()
        , content = this.getContent()
      $tip.find('.popover-title')[this.isHTML(title) ? 'html' : 'text'](title)
      $tip.find('.popover-content > *')[this.isHTML(content) ? 'html' : 'text'](content)
      $tip.removeClass('fade top bottom left right in')
    }
  , hasContent: function () {
      return this.getTitle() || this.getContent()
    }
  , getContent: function () {
      var content
        , $e = this.$element
        , o = this.options
      content = $e.attr('data-content')
        || (typeof o.content == 'function' ? o.content.call($e[0]) :  o.content)
      return content
    }
  , tip: function () {
      if (!this.$tip) {
        this.$tip = $(this.options.template)
      }
      return this.$tip
    }
  })

 /* POPOVER PLUGIN DEFINITION
  * ======================= */
  $.fn.popover = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('popover')
        , options = typeof option == 'object' && option
      if (!data) $this.data('popover', (data = new Popover(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }
  $.fn.popover.Constructor = Popover
  $.fn.popover.defaults = $.extend({} , $.fn.tooltip.defaults, {
    placement: 'right'
  , content: ''
  , template: '<div class="popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
  })
}(window.jQuery);/* =============================================================
 * bootstrap-scrollspy.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#scrollspy
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================== */

!function ($) {
  "use strict"; // jshint ;_;

  /* SCROLLSPY CLASS DEFINITION
   * ========================== */
  function ScrollSpy( element, options) {
    var process = $.proxy(this.process, this)
      , $element = $(element).is('body') ? $(window) : $(element)
      , href
    this.options = $.extend({}, $.fn.scrollspy.defaults, options)
    this.$scrollElement = $element.on('scroll.scroll.data-api', process)
    this.selector = (this.options.target
      || ((href = $(element).attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')) //strip for ie7
      || '') + ' .nav li > a'
    this.$body = $('body')
    this.refresh()
    this.process()
  }
  ScrollSpy.prototype = {
      constructor: ScrollSpy
    , refresh: function () {
        var self = this
          , $targets
        this.offsets = $([])
        this.targets = $([])
        $targets = this.$body
          .find(this.selector)
          .map(function () {
            var $el = $(this)
              , href = $el.data('target') || $el.attr('href')
              , $href = /^#\w/.test(href) && $(href)
            return ( $href
              && href.length
              && [[ $href.position().top, href ]] ) || null
          })
          .sort(function (a, b) { return a[0] - b[0] })
          .each(function () {
            self.offsets.push(this[0])
            self.targets.push(this[1])
          })
      }
    , process: function () {
        var scrollTop = this.$scrollElement.scrollTop() + this.options.offset
          , scrollHeight = this.$scrollElement[0].scrollHeight || this.$body[0].scrollHeight
          , maxScroll = scrollHeight - this.$scrollElement.height()
          , offsets = this.offsets
          , targets = this.targets
          , activeTarget = this.activeTarget
          , i
        if (scrollTop >= maxScroll) {
          return activeTarget != (i = targets.last()[0])
            && this.activate ( i )
        }
        for (i = offsets.length; i--;) {
          activeTarget != targets[i]
            && scrollTop >= offsets[i]
            && (!offsets[i + 1] || scrollTop <= offsets[i + 1])
            && this.activate( targets[i] )
        }
      }
    , activate: function (target) {
        var active
          , selector
        this.activeTarget = target
        $(this.selector)
          .parent('.active')
          .removeClass('active')
        selector = this.selector
          + '[data-target="' + target + '"],'
          + this.selector + '[href="' + target + '"]'
        active = $(selector)
          .parent('li')
          .addClass('active')
        if (active.parent('.dropdown-menu'))  {
          active = active.closest('li.dropdown').addClass('active')
        }
        active.trigger('activate')
      }
  }

 /* SCROLLSPY PLUGIN DEFINITION
  * =========================== */
  $.fn.scrollspy = function ( option ) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('scrollspy')
        , options = typeof option == 'object' && option
      if (!data) $this.data('scrollspy', (data = new ScrollSpy(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }
  $.fn.scrollspy.Constructor = ScrollSpy
  $.fn.scrollspy.defaults = {
    offset: 10
  }
 /* SCROLLSPY DATA-API
  * ================== */
  $(function () {
    $('[data-spy="scroll"]').each(function () {
      var $spy = $(this)
      $spy.scrollspy($spy.data())
    })
  })
}(window.jQuery);/* ========================================================
 * bootstrap-tab.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#tabs
 * ========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ======================================================== */

!function ($) {
  "use strict"; // jshint ;_;

 /* TAB CLASS DEFINITION
  * ==================== */
  var Tab = function ( element ) {
    this.element = $(element)
  }
  Tab.prototype = {
    constructor: Tab
  , show: function () {
      var $this = this.element
        , $ul = $this.closest('ul:not(.dropdown-menu)')
        , selector = $this.attr('data-target')
        , previous
        , $target
        , e
      if (!selector) {
        selector = $this.attr('href')
        selector = selector && selector.replace(/.*(?=#[^\s]*$)/, '') //strip for ie7
      }
      if ( $this.parent('li').hasClass('active') ) return
      previous = $ul.find('.active a').last()[0]
      e = $.Event('show', {
        relatedTarget: previous
      })
      $this.trigger(e)
      if (e.isDefaultPrevented()) return
      $target = $(selector)
      this.activate($this.parent('li'), $ul)
      this.activate($target, $target.parent(), function () {
        $this.trigger({
          type: 'shown'
        , relatedTarget: previous
        })
      })
    }
  , activate: function ( element, container, callback) {
      var $active = container.find('> .active')
        , transition = callback
            && $.support.transition
            && $active.hasClass('fade')
      function next() {
        $active
          .removeClass('active')
          .find('> .dropdown-menu > .active')
          .removeClass('active')
        element.addClass('active')
        if (transition) {
          element[0].offsetWidth // reflow for transition
          element.addClass('in')
        } else {
          element.removeClass('fade')
        }
        if ( element.parent('.dropdown-menu') ) {
          element.closest('li.dropdown').addClass('active')
        }
        callback && callback()
      }
      transition ?
        $active.one($.support.transition.end, next) :
        next()
      $active.removeClass('in')
    }
  }

 /* TAB PLUGIN DEFINITION
  * ===================== */
  $.fn.tab = function ( option ) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('tab')
      if (!data) $this.data('tab', (data = new Tab(this)))
      if (typeof option == 'string') data[option]()
    })
  }
  $.fn.tab.Constructor = Tab

 /* TAB DATA-API
  * ============ */
  $(function () {
    $('body').on('click.tab.data-api', '[data-toggle="tab"], [data-toggle="pill"]', function (e) {
      e.preventDefault()
      $(this).tab('show')
    })
  })
}(window.jQuery);/* =============================================================
 * bootstrap-typeahead.js v2.0.4
 * http://twitter.github.com/bootstrap/javascript.html#typeahead
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */

!function($){
  "use strict"; // jshint ;_;

 /* TYPEAHEAD PUBLIC CLASS DEFINITION
  * ================================= */
  var Typeahead = function (element, options) {
    this.$element = $(element)
    this.options = $.extend({}, $.fn.typeahead.defaults, options)
    this.matcher = this.options.matcher || this.matcher
    this.sorter = this.options.sorter || this.sorter
    this.highlighter = this.options.highlighter || this.highlighter
    this.updater = this.options.updater || this.updater
    this.$menu = $(this.options.menu).appendTo('body')
    this.source = this.options.source
    this.shown = false
    this.listen()
  }
  Typeahead.prototype = {
    constructor: Typeahead
  , select: function () {
      var val = this.$menu.find('.active').attr('data-value')
      this.$element
        .val(this.updater(val))
        .change()
      return this.hide()
    }
  , updater: function (item) {
      return item
    }
  , show: function () {
      var pos = $.extend({}, this.$element.offset(), {
        height: this.$element[0].offsetHeight
      })
      this.$menu.css({
        top: pos.top + pos.height
      , left: pos.left
      })
      this.$menu.show()
      this.shown = true
      return this
    }
  , hide: function () {
      this.$menu.hide()
      this.shown = false
      return this
    }
  , lookup: function (event) {
      var that = this
        , items
        , q
      this.query = this.$element.val()
      if (!this.query) {
        return this.shown ? this.hide() : this
      }
      items = $.grep(this.source, function (item) {
        return that.matcher(item)
      })
      items = this.sorter(items)
      if (!items.length) {
        return this.shown ? this.hide() : this
      }
      return this.render(items.slice(0, this.options.items)).show()
    }
  , matcher: function (item) {
      return ~item.toLowerCase().indexOf(this.query.toLowerCase())
    }
  , sorter: function (items) {
      var beginswith = []
        , caseSensitive = []
        , caseInsensitive = []
        , item
      while (item = items.shift()) {
        if (!item.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(item)
        else if (~item.indexOf(this.query)) caseSensitive.push(item)
        else caseInsensitive.push(item)
      }
      return beginswith.concat(caseSensitive, caseInsensitive)
    }
  , highlighter: function (item) {
      var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
      return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
        return '<strong>' + match + '</strong>'
      })
    }
  , render: function (items) {
      var that = this
      items = $(items).map(function (i, item) {
        i = $(that.options.item).attr('data-value', item)
        i.find('a').html(that.highlighter(item))
        return i[0]
      })
      items.first().addClass('active')
      this.$menu.html(items)
      return this
    }
  , next: function (event) {
      var active = this.$menu.find('.active').removeClass('active')
        , next = active.next()
      if (!next.length) {
        next = $(this.$menu.find('li')[0])
      }
      next.addClass('active')
    }
  , prev: function (event) {
      var active = this.$menu.find('.active').removeClass('active')
        , prev = active.prev()
      if (!prev.length) {
        prev = this.$menu.find('li').last()
      }
      prev.addClass('active')
    }
  , listen: function () {
      this.$element
        .on('blur',     $.proxy(this.blur, this))
        .on('keypress', $.proxy(this.keypress, this))
        .on('keyup',    $.proxy(this.keyup, this))
      if ($.browser.webkit || $.browser.msie) {
        this.$element.on('keydown', $.proxy(this.keypress, this))
      }
      this.$menu
        .on('click', $.proxy(this.click, this))
        .on('mouseenter', 'li', $.proxy(this.mouseenter, this))
    }
  , keyup: function (e) {
      switch(e.keyCode) {
        case 40: // down arrow
        case 38: // up arrow
          break
        case 9: // tab
        case 13: // enter
          if (!this.shown) return
          this.select()
          break
        case 27: // escape
          if (!this.shown) return
          this.hide()
          break
        default:
          this.lookup()
      }
      e.stopPropagation()
      e.preventDefault()
  }
  , keypress: function (e) {
      if (!this.shown) return
      switch(e.keyCode) {
        case 9: // tab
        case 13: // enter
        case 27: // escape
          e.preventDefault()
          break
        case 38: // up arrow
          if (e.type != 'keydown') break
          e.preventDefault()
          this.prev()
          break
        case 40: // down arrow
          if (e.type != 'keydown') break
          e.preventDefault()
          this.next()
          break
      }
      e.stopPropagation()
    }
  , blur: function (e) {
      var that = this
      setTimeout(function () { that.hide() }, 150)
    }
  , click: function (e) {
      e.stopPropagation()
      e.preventDefault()
      this.select()
    }
  , mouseenter: function (e) {
      this.$menu.find('.active').removeClass('active')
      $(e.currentTarget).addClass('active')
    }
  }

  /* TYPEAHEAD PLUGIN DEFINITION
   * =========================== */
  $.fn.typeahead = function (option) {
    return this.each(function () {
      var $this = $(this)
        , data = $this.data('typeahead')
        , options = typeof option == 'object' && option
      if (!data) $this.data('typeahead', (data = new Typeahead(this, options)))
      if (typeof option == 'string') data[option]()
    })
  }
  $.fn.typeahead.defaults = {
    source: []
  , items: 8
  , menu: '<ul class="typeahead dropdown-menu"></ul>'
  , item: '<li><a href="#"></a></li>'
  }
  $.fn.typeahead.Constructor = Typeahead

 /* TYPEAHEAD DATA-API
  * ================== */
  $(function () {
    $('body').on('focus.typeahead.data-api', '[data-provide="typeahead"]', function (e) {
      var $this = $(this)
      if ($this.data('typeahead')) return
      e.preventDefault()
      $this.typeahead($this.data())
    })
  })
}(window.jQuery);
/***********************以下自定义插件***************************/
//date panel
!function($) {
    // Picker object @element: 文本框
    var Datepicker = function(element, options){
        this.element = $(element);
        this.format = DPGlobal.parseFormat(options.format||this.element.data('date-format')||'yyyy-mm-dd');
        DPGlobal.template = DPGlobal.template.replace('{ctime}', DPGlobal.formatTime());
        this.picker = $(DPGlobal.template)
                            .appendTo('body')
                            .on({
                                click: $.proxy(this.click, this),
                                mousedown: $.proxy(this.mousedown, this)
                            });
        this.isInput = this.element.is('input');
        this.component = this.element.is('.date') ? this.element.find('.add-on') : false;
        
        if (this.isInput) {
            this.element.on({
                focus: $.proxy(this.show, this),
                click: $.proxy(this.show, this),
                keyup: $.proxy(this.update, this),
                 // blur: $.proxy(this.hide, this) //不支持ie<=8
                 blur: (!document.all && $.proxy(this.hide, this)) //不支持ieie<=8
                 // ____: 1
            });
        } else {
            if (this.component){
                this.component.on('click', $.proxy(this.show, this));
            } else {
                this.element.on('click', $.proxy(this.show, this));
            }
        }
        //add by cty
        var self = this;
        $('body').on('keyup', function(evt){
            if(27 == evt.keyCode) {
                self.hide();
            }
        });
        //end
        this.viewMode = 0;
        this.weekStart = options.weekStart||this.element.data('date-weekstart')||0;
        this.weekEnd = this.weekStart == 0 ? 6 : this.weekStart - 1;
        this.fillDow();
        this.fillMonths();
        this.update();
        this.showMode();
    };
    
    Datepicker.prototype = {
        constructor: Datepicker,
        
        show: function(e) {
            this.picker.show();
            this.height = this.component ? this.component.outerHeight() : this.element.outerHeight();
            this.place();
            $(window).on('resize', $.proxy(this.place, this));
            if (e ) {
                e.stopPropagation();
                e.preventDefault();
            }
            // if (!this.isInput) {
                // $(document).on('mousedown', $.proxy(this.hide, this));
            // }
            $(document).on('click', $.proxy(this.hide, this));
            this.element.trigger({
                type: 'show',
                date: this.date
            });
            this.update();  //fix by cty
            DPGlobal.formatTimeIntval();
        },
        
        hide: function(){
            this.picker.hide();
            $(window).off('resize', this.place);
            this.viewMode = 0;
            this.showMode();
            if (!this.isInput) {
                $(document).off('mousedown', this.hide);
            }
            // this.setValue();
            this.element.trigger({
                type: 'hide',
                date: this.date
            });
        },
        setValue: function() {
            var formated = DPGlobal.formatDate(this.date, this.format);
            var old = this.element.prop('value');                         //fix by cty
            old = old.replace(/[^0-9\:\-\s]/gi,'').replace(/^\s*/g,'').replace(/\s*$/g,'');
            old = old.replace(/\d{1,4}\-\d{1,2}\-\d{1,2}/, formated); 
            if(old.length < 9) {
                formated += ' ' + old;
            }else {
                formated = old;
            }
            formated = formated.replace(/^\s*/g,'').replace(/\s*$/g,'');
            if (!this.isInput) {
                if (this.component){
                    this.element.find('input').prop('value', formated);
                }
                this.element.data('date', formated);
            } else {
                this.element.prop('value', formated);
            }
        },
        setTime: function(ctime) {
            var old = this.element.prop('value');
            old = old.replace(/[^0-9\:\-\s]/gi,'').replace(/^\s*/g,'').replace(/\s*$/g,'');
            formated = old.replace(/\s*\d{1,2}\:\d{1,2}\:\d{1,2}\s*/, '');
            console.log(old, '--', formated);
            if(formated.length < 14) {
                formated += ' ' + ctime;
            }
            this.element.prop('value', formated);
        },
        
        place: function(){
            var offset = this.component ? this.component.offset() : this.element.offset();
            this.picker.css({
                top: offset.top + this.height,
                left: offset.left
            });
        },
        
        update: function(){
            this.date = DPGlobal.parseDate(
                this.isInput ? this.element.prop('value') : this.element.data('date'),
                this.format
            );
            this.viewDate = new Date(this.date);
            this.fill();
        },
        
        fillDow: function(){
            var dowCnt = this.weekStart;
            var html = '<tr>';
            while (dowCnt < this.weekStart + 7) {
                html += '<th class="dow">'+DPGlobal.dates.daysMin[(dowCnt++)%7]+'</th>';
            }
            html += '</tr>';
            this.picker.find('.datepicker-days thead').append(html);
        },
        
        fillMonths: function(){
            var html = '';
            var i = 0
            while (i < 12) {
                html += '<span class="month">'+DPGlobal.dates.monthsShort[i++]+'</span>';
            }
            this.picker.find('.datepicker-months td').append(html);
        },
        
        fill: function() {
            var d = new Date(this.viewDate),
                year = d.getFullYear(),
                month = d.getMonth(),
                currentDate = this.date.valueOf();
            // this.picker.find('.datepicker-days th:eq(1)').text(DPGlobal.dates.months[month]+' '+year);
            this.picker.find('.datepicker-days th:eq(1)').text(year+' '+DPGlobal.dates.months[month]); //fix by cty
            var prevMonth = new Date(year, month-1, 28,0,0,0,0),
                day = DPGlobal.getDaysInMonth(prevMonth.getFullYear(), prevMonth.getMonth());
            prevMonth.setDate(day);
            prevMonth.setDate(day - (prevMonth.getDay() - this.weekStart + 7)%7);
            var nextMonth = new Date(prevMonth);
            nextMonth.setDate(nextMonth.getDate() + 42);
            nextMonth = nextMonth.valueOf();
            html = [];
            var clsName;
            while(prevMonth.valueOf() < nextMonth) {
                if (prevMonth.getDay() == this.weekStart) {
                    html.push('<tr>');
                }
                clsName = '';
                if (prevMonth.getMonth() < month) {
                    clsName += ' old';
                } else if (prevMonth.getMonth() > month) {
                    clsName += ' new';
                }
                if (prevMonth.valueOf() == currentDate) {
                    clsName += ' active';
                }
                html.push('<td class="day'+clsName+'">'+prevMonth.getDate() + '</td>');
                if (prevMonth.getDay() == this.weekEnd) {
                    html.push('</tr>');
                }
                prevMonth.setDate(prevMonth.getDate()+1);
            }
            this.picker.find('.datepicker-days tbody').empty().append(html.join(''));
            var currentYear = this.date.getFullYear();
            
            var months = this.picker.find('.datepicker-months')
                        .find('th:eq(1)')
                            .text(year)
                            .end()
                        .find('span').removeClass('active');
            if (currentYear == year) {
                months.eq(this.date.getMonth()).addClass('active');
            }
            
            html = '';
            year = parseInt(year/10, 10) * 10;
            var yearCont = this.picker.find('.datepicker-years')
                                .find('th:eq(1)')
                                    .text(year + '-' + (year + 9))
                                    .end()
                                .find('td');
            year -= 1;
            for (var i = -1; i < 11; i++) {
                html += '<span class="year'+(i == -1 || i == 10 ? ' old' : '')+(currentYear == year ? ' active' : '')+'">'+year+'</span>';
                year += 1;
            }
            yearCont.html(html);
        },
        
        click: function(e) {
            e.stopPropagation();
            e.preventDefault();
            var target = $(e.target).closest('span, td, th, input');
            if (target.length == 1) {
                switch(target[0].nodeName.toLowerCase()) {
                    case 'input':
                        this.setTime(target.val());
                        // alert(target.val());
                        this.show();
                        break;
                    case 'th':
                        switch(target[0].className) {
                            case 'switch':
                                this.showMode(1);
                                break;
                            case 'prev':
                            case 'next':
                                this.viewDate['set'+DPGlobal.modes[this.viewMode].navFnc].call(
                                    this.viewDate,
                                    this.viewDate['get'+DPGlobal.modes[this.viewMode].navFnc].call(this.viewDate) + 
                                    DPGlobal.modes[this.viewMode].navStep * (target[0].className == 'prev' ? -1 : 1)
                                );
                                this.fill();
                                break;
                            case 'ctime':
                                this.setTime(target.text());
                                break;
                        }
                        break;
                    case 'span':
                        if (target.is('.month')) {
                            var month = target.parent().find('span').index(target);
                            this.viewDate.setMonth(month);
                        } else {
                            var year = parseInt(target.text(), 10)||0;
                            this.viewDate.setFullYear(year);
                        }
                        this.showMode(-1);
                        this.fill();
                        break;
                    case 'td':
                        if (target.is('.day')){
                            var day = parseInt(target.text(), 10)||1;
                            var month = this.viewDate.getMonth();
                            if (target.is('.old')) {
                                month -= 1;
                            } else if (target.is('.new')) {
                                month += 1;
                            }
                            var year = this.viewDate.getFullYear();
                            this.date = new Date(year, month, day,0,0,0,0);
                            this.viewDate = new Date(year, month, day,0,0,0,0);
                            this.fill();
                            this.setValue();
                            this.element.trigger({
                                type: 'changeDate',
                                date: this.date
                            });
                        }
                        break;
                }
            }
        },
        
        mousedown: function(e){
            e.stopPropagation();
            e.preventDefault();
        },
        
        showMode: function(dir) {
            if (dir) {
                this.viewMode = Math.max(0, Math.min(2, this.viewMode + dir));
            }
            this.picker.find('>div').hide().filter('.datepicker-'+DPGlobal.modes[this.viewMode].clsName).show();
        }
    };
    
    $.fn.datepicker = function(option) {
        return this.each(function(){
            var $this = $(this),
                data = $this.data('datepicker'),
                options = typeof option == 'object' && option;
            if (!data) {
                $this.data('datepicker', (data = new Datepicker(this, $.extend({}, $.fn.datepicker.defaults,options))));
            }
            if (typeof option == 'string') data[option]();
        });
    };
    $.fn.datepicker.defaults = {};
    $.fn.datepicker.Constructor = Datepicker;
    
    var DPGlobal = {
        modes: [
            {
                clsName: 'days',
                navFnc: 'Month',
                navStep: 1
            },
            {
                clsName: 'months',
                navFnc: 'FullYear',
                navStep: 1
            },
            {
                clsName: 'years',
                navFnc: 'FullYear',
                navStep: 10
        }],
        dates:{
            /*days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
            daysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
            months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]*/
            days: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
            daysShort: ["日", "一", "二", "三", "四", "五", "六", "日"],
            daysMin: ["日", "一", "二", "三", "四", "五", "六", "日"],
            months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
            monthsShort: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"]
        },
        isLeapYear: function (year) {
            return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0))
        },
        getDaysInMonth: function (year, month) {
            return [31, (DPGlobal.isLeapYear(year) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month]
        },
        parseFormat: function(format){
            var separator = format.match(/[.\/-].*?/),
                parts = format.split(/\W+/);
            if (!separator || !parts || parts.length == 0){
                throw new Error("Invalid date format.");
            }//val
            return {separator: separator, parts: parts};
        },
        parseDate: function(date, format) {
            date   = date.replace(/\s*\d{1,2}\:\d{1,2}\:\d{1,2}/, ''); //去掉时间
            if('0000-00-00' == date) date = '';
            var dt = new Date();
            var _y = dt.getFullYear();
            var _m = dt.getMonth();
            var _d = dt.getDate();
            var parts = date.split(format.separator),
                date = new Date(_y, _m, _d, 0, 0, 0),
                val;
            if (parts.length == format.parts.length) {
                for (var i=0, cnt = format.parts.length; i < cnt; i++) {
                    val = parseInt(parts[i], 10)||1;
                    switch(format.parts[i]) {
                        case 'dd':
                        case 'd':
                            date.setDate(val);
                            break;
                        case 'mm':
                        case 'm':
                            date.setMonth(val - 1);
                            break;
                        case 'yy':
                            date.setFullYear(2000 + val);
                            break;
                        case 'yyyy':
                            date.setFullYear(val);
                            break;
                    }
                }
            }
            return date;
        },
        formatDate: function(date, format){
            var val = {
                d: date.getDate(),
                m: date.getMonth() + 1,
                yy: date.getFullYear().toString().substring(2),
                yyyy: date.getFullYear()
            };
            val.dd = (val.d < 10 ? '0' : '') + val.d;
            val.mm = (val.m < 10 ? '0' : '') + val.m;
            var date = [];
            for (var i=0, cnt = format.parts.length; i < cnt; i++) {
                date.push(val[format.parts[i]]);
            }
            return date.join(format.separator);
        },
        formatTime: function(date){
            if(date) {
                var T = new Date(date);
            }else{
                var T = new Date();
            }
            var h = ''+T.getHours();
            var m = ''+T.getMinutes();
            var s = ''+T.getSeconds();
            return (h.length<2?'0'+h:h)+':' +(m.length<2?'0'+m:m)+ ':'+(s.length<2?'0'+s:s);
        },
        formatTimeIntval: function(){
            if('undefined' != typeof(__INTTIME_FWE1F15FSD2F1)) return;
            var self = this;
            __INTTIME_FWE1F15FSD2F1 = setInterval(function(){
              $('.datepicker').find("th[data-ctime]").html(self.formatTime());
            }, 1000);
            
        },
        headTemplate: '<thead>'+
                            '<tr>'+
                                '<th class="prev"><i class="icon-arrow-left">◄</i></th>'+
                                '<th colspan="5" class="switch"></th>'+
                                '<th class="next"><i class="icon-arrow-right">►</i></th>'+
                            '</tr>'+
                            '<tr>'+
                                '<th colspan="2" class="ctime">00:00:00</th>'+
                                /*'<th colspan="2" class="ctime"><input value="{ctime}" onclick="this.focus()" style="width:60px;padding:0;margin:0;border:0;background:none;outline:none;" /></th>'+*/
                                '<th colspan="3" class="ctime" data-ctime="1">{ctime}</th>'+
                                '<th colspan="2" class="ctime">23:59:59</th>'+
                            '</tr>'+
                        '</thead>',
        contTemplate: '<tbody><tr><td colspan="7"></td></tr></tbody>'
    };
    DPGlobal.headTemplate = DPGlobal.headTemplate.replace('{ctime}', DPGlobal.formatTime());
    DPGlobal.template = '<div class="datepicker" style="width:280px;">'+
                            '<div class="datepicker-days">'+
                                '<table>'+
                                    DPGlobal.headTemplate+
                                    '<tbody></tbody>'+
                                '</table>'+
                            '</div>'+
                            '<div class="datepicker-months">'+
                                '<table>'+
                                    DPGlobal.headTemplate+
                                    DPGlobal.contTemplate+
                                '</table>'+
                            '</div>'+
                            '<div class="datepicker-years">'+
                                '<table>'+
                                    DPGlobal.headTemplate+
                                    DPGlobal.contTemplate+
                                '</table>'+
                            '</div>'+
                        '</div>';
    // DPGlobal.template = DPGlobal.template.replace('{ctime}', DPGlobal.formatTime());
}(window.jQuery);
jQuery.cookie = function (key, value, options) {
  if (arguments.length > 1 && String(value) !== "[object Object]") {
    options = jQuery.extend({}, options);
    if (value === null || value === undefined) {
      options.expires = -1;
    }
    if (typeof options.expires === 'number') {
      var days = options.expires, t = options.expires = new Date();
      t.setDate(t.getDate() + days);
    }
    value = String(value);
    return (document.cookie = [
      encodeURIComponent(key), '=',
      options.raw ? value : encodeURIComponent(value),
      options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
      options.path ? '; path=' + options.path : '',
      options.domain ? '; domain=' + options.domain : '',
      options.secure ? '; secure' : ''
    ].join(''));
  }
  // key and possibly options given, get cookie...
  options = value || {};
  var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
  return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};
//drag
;(function($){
    $.fn.extend({
        drag: function(){
            jObj = this;
            var pos = jObj.offset();
            jObj.dnX = pos.left;      //鼠标按下时的x坐标
            jObj.dnY = pos.top;       //鼠标按下时的y坐标
            // alert(this.offset().left);
            jObj.moved = false;
            jObj.on('mousedown', jObj.dragDown);
            $(document).on('mousemove', jObj.dragMove);
            $(document).on('mouseup',   jObj.dragEnd);
            jObj.find(':header').mousedown(function(evt){
                evt.preventDefault();
            });
        },
        dragDown: function(evt){
            // alert(_this.moved);
            jObj = $(this);           //些名非常重要,此表明了要拖拽对象,不至于被设置dragable的对象一起拖动
            // evt.preventDefault();
            // alert(jObj.attr('id'));
            var TN = evt.target.tagName;
            if(0 === TN.indexOf('H')) {
                jObj.moved = true;  //只有<Hn>才拖动
            }else{
                jObj.moved = false; //bootstrap特殊处理
            }
            
            jObj.msdnX = evt.clientX;  //鼠标按下时的位置
            jObj.msdnY = evt.clientY;  //鼠标按下时的位置
            // alert('mousedown:'+ evt.clientX);
            window.status = 'mousedown:'+ evt.clientX;
        },
        dragMove: function(evt){
            // alert(this.moved);
            jObj.msmvX = evt.clientX;  //鼠标移动时的位置
            jObj.msmvY = evt.clientY;  //鼠标移动时的位置
            if(jObj.moved){
                var deltaX = jObj.msmvX - jObj.msdnX;
                var deltaY = jObj.msmvY - jObj.msdnY;
                var left = parseInt(jObj.offset().left) + deltaX;
                var top  = parseInt(jObj.offset().top)  + deltaY;
                jObj.css({left:left,top:top});
                // alert('mousemove:'+ deltaX);
                jObj.msdnX = jObj.msmvX;
                jObj.msdnY = jObj.msmvY;
            }
        },
        dragEnd:  function(){
            jObj.moved = false;
        }
    });
})(jQuery);
//end drag
//flash box
;(function($){
    $.extend({
        popBox: function(opts){
            opts = opts || {}
            var id   = '__showflashbox__1_2_3';
            if($('#'+id).length > 0) return;
            var msg  = opts['msg'] || '消息提示!';
            var ac   = 'undefined'==typeof(opts['ac']) ? true : opts['ac']; //auto close
            if(!ac) opts['butx'] = true;
            var butX = opts['butx'] ? "<button class='close' data-dismiss='alert' type='butto'>×</button>" : '';
            var __cls = opts['cls'] || 'alert-success';
            var html = '<div id="__id" class="alert __cls" style="white-space:nowrap;">\
                          __close\
                          <h4>__msg</h4>\
                        </div>'.replace('__id',id).replace('__close',butX).replace('__msg',msg).replace('__cls',__cls);
            var top  = $('body').height()/4 + document.body.scrollTop+80;
            // alert(top)
            var self = $('body').append(html).find('#'+id);
            var left = ($('body').width()-msg.length * 12)/3;
                left = left<32 ? 32 : left;
            self.css({position:'absolute', 'top':top, left:left, zIndex:99999});
            if(ac) {
                window.setTimeout(function(){
                    self.fadeOut(500, function(){self.remove()});
                },2000);
            }
        },
        showFlashBox: function(opts){
            opts = opts || {}
            opts['ac'] = true; //自动关闭
            this.popBox(opts);
        },
        showAlterBox: function(opts){
            opts = opts || {}
            opts['ac']  = true; //自动关闭
            opts['cls'] = 'alert-info';
            this.popBox(opts);
        },
        showConfirmBox: function(opts){
            opts = opts || {};
            opts['ac'] = false; //自动关闭
            opts['cls'] = 'alert-error';
            this.popBox(opts);
        }
    });
})(window.jQuery);
//保存/提取全局变量
;(function($){
    $.extend({
        gSet: function(k,v){
            $(document).data(k,v);
        },
        gGet: function(k){
            return $(document).data(k);
        }
    });
})(jQuery);