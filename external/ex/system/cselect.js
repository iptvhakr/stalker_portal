/**
 *
 * @param parent
 * @param parameters
 * @constructor
 * @abstract
 */
function CSimpleSelect (parent, parameters){
	this.parameters = parameters || {};
	this.tagName = "SELECT";
	/**
	 * Default values for element initialization
	 * @type {Object}
	 */
	this.attributes = {
		content: {
			className: "csimple-select-content"
		},
		left_arrow: {
			className: "csimple-select-arrow left"
		},
		right_arrow: {
			className: "csimple-select-arrow right"
		},
		wrapper: {
			className: "csimple-select-wrapper",
			tabIndex: "1"
		},
		content_wrapper: {
			className: "csimple-content-wrapper"
		},
		hint: {
			className: "csimple-select-hint csimple-select-content"
		}
	};

	this.style = parameters.style || "";

	this.type = "select";

	if (parameters.attributes !== undefined){
		extend(this.attributes, parameters.attributes);
	}

	this.bind(parameters.events || {});

	CBase.call(this, parent || null);

	if (!(this.parentNode = parameters.parent)){
		if (parameters.content === undefined)
			this.parentNode = element("div");
		else
			this.parentNode = parameters.content.parentNode;
	}
}

// extending
CSimpleSelect.prototype = Object.create(CBase.prototype);
CSimpleSelect.prototype.constructor = CSimpleSelect;

CSimpleSelect.prototype.GenerateHandle = function(){
	var self = this, wrapper, content_wrapper;
	if (this.parameters.content !== undefined && this.parameters.content.tagName.toLowerCase() === "div"){
		this.content = this.parameters.content;
		this.content.parentNode.removeChild(this.content);
		if (this.content.className !== undefined)
			this.attributes.content.className += " " + this.content.className;
		this.content = element("div", this.attributes.content,  this.content || "");
	}else{
		if (this.parameters.content !== undefined && this.parameters.content.tagName.toLowerCase() !== "div")
			this.content = this.parameters.content;
		this.content = element("div", this.attributes.content,  this.content || "");
	}
	content_wrapper = element("div", this.attributes.content_wrapper, [this.content, this.hint = element("div", this.attributes.hint)]);
	if (this.parameters.leftArrow){
		this.leftArrow = this.parameters.leftArrow;
		this.leftArrow.parentNode.removeChild(this.leftArrow);
		this.leftArrow = element("div", this.attributes.left_arrow, this.leftArrow);
	}else{
		this.leftArrow = element("div", this.attributes.left_arrow, this.leftArrow || element("p"));
	}
	if (this.parameters.rightArrow){
		this.rightArrow = this.parameters.rightArrow;
		this.rightArrow.parentNode.removeChild(this.rightArrow);
		this.rightArrow = element("div", this.attributes.right_arrow, this.rightArrow);
	}else{
		this.rightArrow = element("div", this.attributes.right_arrow, this.rightArrow || element("p"));
	}
	wrapper = element("div", this.attributes.wrapper, element("div", {className: "content-wrapper", onfocus:function(){}}, [this.leftArrow, content_wrapper, this.rightArrow]));
	wrapper.className += " " + this.style;
	if (this.parameters.width !== undefined){
		wrapper.style.width = this.parameters.width;
	}
	this.leftArrow.onclick = function(event){if (self.disabled) return; self.Previous(); event.stopPropagation(); event.preventDefault()};
	this.rightArrow.onclick = function(event){if (self.disabled) return; self.Next(); event.stopPropagation(); event.preventDefault()};
	return wrapper;
};

CSimpleSelect.prototype.onInit = function(){
	var self = this;
	elchild(this.parentNode, this.handle);
	this.handle.component = this;
	this.handle.onblur = function(){
		self.trigger("onBlur");
	};
	this.handle.onfocus = function(){
		self.trigger("onFocus");
	}
};

CSimpleSelect.prototype.EventHandler = function(event){
	echo('event.code=>'+event.code);
	eventPrepare(event, true, 'CSimpleSelect');
	if (this.disabled === true)
		return;
	event.stopped = true;
	echo('event.code=>'+event.code);
	switch (event.code){
		case KEYS.LEFT:
			this.Previous();
			break;
		case KEYS.RIGHT:
			this.Next();
			break;
		default :
			event.stopped = false;
	}
};

CSimpleSelect.prototype.SetContent = function(content){
	this.content.innerHTML = "";
	this.content.innerHTML = content;
};

CSimpleSelect.prototype.DisableElement = function(element, disable) {
	if (disable === true){
		if (element.className.indexOf("disabled") === -1) element.className += " disabled";
	}else{
		element.className = element.className.replace(" disabled", "");
	}
};

CSimpleSelect.prototype.SetHint = function( hint ){
	this.hint.innerHTML = hint;
};

CSimpleSelect.prototype.Next = function(){
};

CSimpleSelect.prototype.Previous = function(){
	this.autocomplete = "";
};

CSimpleSelect.prototype.focus = function(){
	this.handle.focus();
};


CSimpleSelect.prototype.blur = function(){
	this.handle.blur();
};

CSimpleSelect.prototype.IsFocused = function(){
	return document.activeElement === this.handle;
};

CSimpleSelect.prototype.Disable = function(disable){
	this.DisableElement(this.handle, (this.disabled = disable === true));
};

Events.inject(CSimpleSelect);

/**
 *
 * @param parent
 * @param parameters
 * @constructor
 */
function CSelectBox(parent, parameters){
	CSimpleSelect.call(this, parent || null, parameters);

	this.attributes.wrapper.className += " cselect-box";

	this.Init(this.GenerateHandle());

	var _data = parameters.data || [],
		_selectedIndex,_default,
		idField = parameters.idField || "id",
		nameField = parameters.nameField || "name",
		_autocomplete = "",self = this;

	this.name = parameters.name || "CSelectBox";

	this.dropdown = new CSelectDropdown(this, {
		select: this,
		scrollMode: 2,
		container: parameters.container
	});

	this.SetData = function(data){
		if (data.length === 0)
			throw "data cannot be empty";
		_data = data || [];
		if (data.length - 1 < _selectedIndex )
			_selectedIndex = data.length - 1;
		self.RefreshContent();
		self.trigger("dataChanged");
	};

	this.GetNameField = function(){
		return nameField;
	};

	this.GetIdField = function(){
		return idField;
	};

	this.GetData = function(){
		return _data;
	};

	this.Autocomplete = function( keyCode ){
//		var find = false, text = "";
//		if (keyCode === KEYS.BACK){
//			text = _autocomplete.slice(0, -1);
//		}else{
//			text = _autocomplete + String.fromCharCode(keyCode).toLowerCase();
//		}
//		if (text !== ""){
//			for (var i = 0, el; i < _data.length, el = _data[i]; i++){
//				if (typeof el === "object"){
//					el = el[nameField];
//				}
//				if (el.toLowerCase().indexOf(text) === 0){
//					this.SetIndex(i);
//					find = true;
//					break;
//				}
//			}
//			if (find === true){
//				self.handle.classList.add("autocomplete");
//				_autocomplete = text;
//			}
//			text = this.GetLabel().substr(0, _autocomplete.length);
//			self.SetHint(text);
//		}else{
//			self.ClearAutocomplete();
//		}
	};

	this.ClearAutocomplete = function(){
//		self.handle.classList.remove("autocomplete");
//		self.SetHint(_autocomplete = "");
	};

	this.GetSelected = function(){
		return _data[_selectedIndex];
	};

	this.Next = function(){
		if (_selectedIndex < _data.length - 1){
			this.SetIndex(++_selectedIndex);
		}
	};

	this.Previous = function(){
		if (_selectedIndex !== 0){
			this.SetIndex(--_selectedIndex);
		}
	};

	this.GetValue = function(){
		var selected =	self.GetSelected();
		return typeof selected === "object" ?selected[idField] : selected;
	};

	this.GetLabel = function(){
		var selected = self.GetSelected();
		return typeof selected === "object" ?selected[nameField] : selected;
	};

	this.RefreshContent = function(){
		self.SetContent(self.GetLabel());
		self.value = self.GetValue(); // remove after system settings change
		if (self.GetData().length === 1) {
			self.DisableElement(self.leftArrow, true);
			self.DisableElement(self.rightArrow, true);
			return;
		}
		if (self.GetIndex() === 0) {
			self.DisableElement(self.leftArrow, true);
			self.DisableElement(self.rightArrow, false);
		} else if (self.GetIndex() === self.GetData().length - 1) {
			self.DisableElement(self.rightArrow, true);
			self.DisableElement(self.leftArrow, false);
		} else {
			self.DisableElement(self.leftArrow, false);
			self.DisableElement(self.rightArrow, false);
		}
	};

	this.SelectById = function(id, quiet) {
		for (var i = 0; i < _data.length; i++){
			if (_data[i][idField] === id){
				self.SetIndex(i, quiet);
				return i;
			}
		}
	};

	this.IsChanged = function(){
		return _selectedIndex !== _default;
	};

	this.GetIndex = function(){
		return _selectedIndex;
	};

	this.SetIndex = function(index, quiet){
		var old = this.GetSelected();
		self.ClearAutocomplete();
		if ( index === undefined || index >= _data.length )
			_selectedIndex = 0;
		else
			_selectedIndex = index;
		self.RefreshContent();
		if (quiet !== true)
			self.trigger("onChange", {"new": self.GetSelected(), old: old});
	};

//	if (parameters.selected)
		this.SetIndex(parameters.selected, true);

	if ( parameters.selectedId !== undefined)
		this.SelectById(parameters.selectedId, true);

	_default = _selectedIndex;

	this.EventHandler = function (event) {
		eventPrepare(event);
		event.stopped = true;
		switch (event.code){
			case KEYS.FRAME:
				this.dropdown.Show(!this.dropdown.isVisible, false);
			break;
			case KEYS.LEFT:
			case KEYS.RIGHT:
				if (!this.dropdown.isVisible){
					CSimpleSelect.prototype.EventHandler.call(this, event);
					this.dropdown.EventHandler(event);
				}
				break;
			case KEYS.OK:
			case KEYS.UP:
			case KEYS.DOWN:
			case KEYS.PAGE_UP:
			case KEYS.PAGE_DOWN:
				event.stopped = false;
				if (this.dropdown.isVisible){
					this.dropdown.EventHandler(event, null, false);
					event.stopped = true;
				}
				break;
			case KEYS.EXIT:
				if (this.dropdown.isVisible){
					this.dropdown.Show(false, false);
				}else{
					event.stopped = false;
				}
				break;
			default :
				event.stopped = false;
		}
	};

	this.handle.onkeypress = function( event ){
		if (event.ctrlKey !== true && event.altKey !== true)
			self.Autocomplete(event.charCode);
	};

	this.dropdown.Refresh();

	this.bind("onBlur", function(){
		self.ClearAutocomplete();
	});

}

// extending
CSelectBox.prototype = Object.create(CSimpleSelect.prototype);
CSelectBox.prototype.constructor = CSelectBox;

function CSelectDropdown(parent, options){
	CScrollList.call(this, parent || null, options);
	var self = this;

	this.isVisible = false;
	this.manageFocus = false;

	this.multipleSelection = false;

	this.attributes = {
		wrapper: {
			className: "csimple-select-dropdown"
		},
		element: {
			className: "dropdown-element"
		}
	};
	this.select = null;

	this.baseClass = "csimple-dropdown-main";

	CSelectDropdown.parameters.forEach(function(key){
		if (options[key] !== undefined)
			self[key] = options[key];
	});

	this.base = this.select.handle;

	this.Init(element("div", this.attributes.wrapper));

	this.base.parentNode.appendChild(this.handle);

	this.select.bind("dataChanged", function(){
		self.Refresh();
	});

	this.select.bind("onChange", function(){
		if (self.isVisible){
			var item  = self.handleInner.children[this.GetIndex()];
			self.Focused(item, true);
			self.Marked(item, true);
			self.SetPosition(item);
		}
	});

	this.select.bind("onBlur", function(){
		setTimeout(function(){
			self.Show(false, self.manageFocus);
		},100);
	});

	this.select.handle.addEventListener("click", function( event ){
		self.Show(!self.isVisible, self.manageFocus);
		event.stopPropagation();
		event.stopImmediatePropagation();
		event.preventDefault();
	});

	this.Show(false, self.manageFocus);
}

// extending
CSelectDropdown.prototype = Object.create(CScrollList.prototype);
CSelectDropdown.prototype.constructor = CSelectDropdown;

CSelectDropdown.parameters = ['scrollMode', 'select', 'container'];

CSelectDropdown.prototype.Refresh = function(){
	var self = this,
		data = this.select.GetData(), el, i;
		this.Clear();
	for (i = 0; i < data.length, el = data[i]; i++){
		if (typeof el === "object"){
			el = el[this.select.GetNameField()];
		}
		this.Add(element("div", extend(this.attributes.element, {innerHTML: el})), {
			onclick: function(){
				self.select.SetIndex(self.activeItem.index);
				self.Marked(self.activeItem, true);
				self.Show(false, self.manageFocus);
			},
			index: i
		});
	}
};

CSelectDropdown.prototype.onShow = function(){
	this.UpdateGeometry();
	var item  = this.handleInner.children[this.select.GetIndex()];
	this.Focused(item, true);
	this.Marked(item, true);
	this.SetPosition(item);
};

CSelectDropdown.prototype.UpdateGeometry = function (){
	var inputRect = this.base.getBoundingClientRect(),
		top = (this.base.offsetTop + inputRect.height),
		height = WINDOW_HEIGHT, containerRect;
	if (this.container !== undefined){
		containerRect = this.container.getBoundingClientRect();
		height = containerRect.top + containerRect.height;
	}

	if (inputRect.top + this.itemHeight * this.itemsPerPage > height) {
		top = this.base.offsetTop - this.itemHeight * this.itemsPerPage;
	}
	if ( this.scrollMode === 1 )
		top -= this.itemHeight * Math.abs(Math.floor(this.itemsPerPage / 2 * -1));
	this.handle.style.top = top + "px";
	this.handle.style.left = this.base.offsetLeft + "px";
	this.handle.style.width = (inputRect.width - parseInt(document.defaultView.getComputedStyle(this.handle, "")["border-left-width"]) * 2)+ "px";
};

Events.inject(CSelectDropdown);

/**
 *
 * @param parent
 * @param parameters
 * @constructor
 */
function CIntervalBox(parent, parameters){
	var self = this,
		_value = 0, _default;
	CSimpleSelect.call(this, parent || null, parameters);

	this.attributes.wrapper.className += " cinterval-box";

	if (parameters.align === undefined)
		this.attributes.content.className += " center";
	if (parameters.contentStyle !== undefined){
		this.attributes.content.className += " "+parameters.contentStyle;
	}
	this.Init(this.GenerateHandle());

	this.name = parameters.name || "input";

	this.type = "interval";

	this.parameters = parameters;
	if (parameters.align !== undefined){
		this.content.className += " "+parameters.align;
		this.content.style.textAlign = parameters.align;
	}
	this.max = parameters.max;
	this.min = parameters.min;
	this.interval = parameters.interval || 1;

	if (parameters.value !== undefined){
		if (this.max !== undefined && parameters.value > this.max){
			_value = this.max;
		} else if (this.min !== undefined && parameters.value < this.min){
			_value = this.min;
		}else{
			_value = parameters.value;
		}
	}else{
		if (this.min !== undefined){
			_value = this.min;
		}else if (this.max !== undefined){
			_value = this.max;
		}
	}

	_default = _value = parseInt(_value);

	this.SetValue = function(value){
		_value = parseInt(value);
		self.SetContent(_value);
		self.trigger("onChange", _value);
	};

	this.GetValue = function(){
		return _value;
	};

	this.Next = function(){
		CSimpleSelect.prototype.Next.call();
		if (self.max !== undefined){
			if (_value + self.interval <= self.max){
				_value += self.interval;
			}else{
				return;
			}
		}else{
			_value += self.interval;
		}
		self.RefreshContent();
		self.trigger("onNext", self.GetValue());
		self.trigger("onChange", self.GetValue());
	};

	this.Previous = function(){
		CSimpleSelect.prototype.Next.call();
		if (self.min !== undefined){
			if (_value - self.interval >= self.min){
				_value -= self.interval;
			}else{
				return;
			}
		}else{
			_value -=self.interval;
		}
		self.RefreshContent();
		self.trigger("onPrevious", self.GetValue());
		self.trigger("onChange", self.GetValue());
	};
	this.RefreshContent = function(){
		this.SetContent(self.GetValue());
		self.value = self.GetValue(); // remove after system settings change
		self.DisableElement(self.leftArrow, false);
		self.DisableElement(self.rightArrow, false);
		if (self.min !== undefined && _value - self.interval < self.min) {
			self.DisableElement(self.leftArrow, true);
		}
		if (self.max !== undefined && _value + self.interval > self.max) {
			self.DisableElement(self.rightArrow, true);
		}
	};

	this.IsChanged = function(){
		return _value !== _default;
	};

	this.RefreshContent();

}
// extending
CIntervalBox.prototype = Object.create(CSimpleSelect.prototype);
CIntervalBox.prototype.constructor = CIntervalBox;
