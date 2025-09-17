/*
 Copyright (c) 2003-2017, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.md or http://ckeditor.com/license
*/
CKEDITOR.dialog.add("cellProperties", function(t) {
	function e(o) {
		return function(t) {
			for(var e = o(t[0]), i = 1; i < t.length; i++)
				if(o(t[i]) !== e) {
					e = null;
					break
				}
			void 0 !== e && (this.setValue(e), CKEDITOR.env.gecko && "select" == this.type && !e && (this.getInputElement().$.selectedIndex = -1))
		}
	}

	function o(t) {
		if(t = a.exec(t.getStyle("width") || t.getAttribute("width"))) return t[2]
	}
	var i = t.lang.table,
		l = i.cell,
		n = t.lang.common,
		r = CKEDITOR.dialog.validate,
		a = /^(\d+(?:\.\d+)?)(px|%)$/,
		s = {
			type: "html",
			html: "&nbsp;"
		},
		u = "rtl" == t.lang.dir,
		g = t.plugins.colordialog;
	return {
		title: l.title,
		minWidth: CKEDITOR.env.ie && CKEDITOR.env.quirks ? 450 : 410,
		minHeight: CKEDITOR.env.ie && (CKEDITOR.env.ie7Compat || CKEDITOR.env.quirks) ? 230 : 220,
		contents: [{
			id: "info",
			label: l.title,
			accessKey: "I",
			elements: [{
				type: "hbox",
				widths: ["40%", "5%", "40%"],
				children: [{
					type: "vbox",
					padding: 0,
					children: [{
						type: "hbox",
						widths: ["70%", "30%"],
						children: [{
							type: "text",
							id: "width",
							width: "100px",
							label: n.width,
							validate: r.number(l.invalidWidth),
							onLoad: function() {
								var t = this.getDialog().getContentElement("info", "widthType").getElement(),
									e = this.getInputElement(),
									i = e.getAttribute("aria-labelledby");
								e.setAttribute("aria-labelledby", [i, t.$.id].join(" "))
							},
							setup: e(function(t) {
								var e = parseInt(t.getAttribute("width"), 10);
								return t = parseInt(t.getStyle("width"), 10), isNaN(t) ? isNaN(e) ? "" : e : t
							}),
							commit: function(t) {
								var e = parseInt(this.getValue(), 10),
									i = this.getDialog().getValueOf("info", "widthType") || o(t);
								isNaN(e) ? t.removeStyle("width") : t.setStyle("width", e + i), t.removeAttribute("width")
							},
							default: ""
						}, {
							type: "select",
							id: "widthType",
							label: t.lang.table.widthUnit,
							labelStyle: "visibility:hidden",
							default: "%",
							items: [
								[i.widthPc, "%"]
							],
							setup: e(o)
						}]
					}, {
						type: "hbox",
						widths: ["70%", "30%"],
						children: [{
							type: "text",
							id: "height",
							label: n.height,
							width: "50%",
							default: "",
							validate: r.number(l.invalidHeight),
							onLoad: function() {
								var t = this.getDialog().getContentElement("info", "htmlHeightType").getElement(),
									e = this.getInputElement(),
									i = e.getAttribute("aria-labelledby");
								e.setAttribute("aria-labelledby", [i, t.$.id].join(" "))
							},
							setup: e(function(t) {
								var e = parseInt(t.getAttribute("height"), 10);
								return t = parseInt(t.getStyle("height"), 10), isNaN(t) ? isNaN(e) ? "" : e : t
							}),
							commit: function(t) {
								var e = parseInt(this.getValue(), 10);
								isNaN(e) ? t.removeStyle("height") : t.setStyle("height", CKEDITOR.tools.cssLength(e)), t.removeAttribute("height")
							}
						}]
					}, s, {
						type: "select",
						id: "wordWrap",
						label: l.wordWrap,
						default: "yes",
						items: [
							[l.yes, "yes"],
							[l.no, "no"]
						],
						setup: e(function(t) {
							var e = t.getAttribute("noWrap");
							if("nowrap" == t.getStyle("white-space") || e) return "no"
						}),
						commit: function(t) {
							"no" == this.getValue() ? t.setStyle("white-space", "nowrap") : t.removeStyle("white-space"), t.removeAttribute("noWrap")
						}
					}, s, {
						type: "select",
						id: "hAlign",
						label: l.hAlign,
						default: "",
						items: [
							[n.notSet, ""],
							[n.alignLeft, "left"],
							[n.alignCenter, "center"],
							[n.alignRight, "right"],
							[n.alignJustify, "justify"]
						],
						setup: e(function(t) {
							var e = t.getAttribute("align");
							return t.getStyle("text-align") || e || ""
						}),
						commit: function(t) {
							var e = this.getValue();
							e ? t.setStyle("text-align", e) : t.removeStyle("text-align"), t.removeAttribute("align")
						}
					}, {
						type: "select",
						id: "vAlign",
						label: l.vAlign,
						default: "",
						items: [
							[n.notSet, ""],
							[n.alignTop, "top"],
							[n.alignMiddle, "middle"],
							[n.alignBottom, "bottom"]
						],
						setup: e(function(t) {
							var e = t.getAttribute("vAlign");
							switch(t = t.getStyle("vertical-align")) {
								case "top":
								case "middle":
								case "bottom":
									break;
								default:
									t = ""
							}
							return t || e || ""
						}),
						commit: function(t) {
							var e = this.getValue();
							e ? t.setStyle("vertical-align", e) : t.removeStyle("vertical-align"), t.removeAttribute("vAlign")
						}
					}]
				}, s, {
					type: "vbox",
					padding: 0,
					children: [{
						type: "select",
						id: "cellType",
						label: l.cellType,
						default: "td",
						items: [
							[l.data, "td"],
							[l.header, "th"]
						],
						setup: e(function(t) {
							return t.getName()
						}),
						commit: function(t) {
							t.renameNode(this.getValue())
						}
					}, s, {
						type: "text",
						id: "rowSpan",
						label: l.rowSpan,
						default: "",
						validate: r.integer(l.invalidRowSpan),
						setup: e(function(t) {
							if((t = parseInt(t.getAttribute("rowSpan"), 10)) && 1 != t) return t
						}),
						commit: function(t) {
							var e = parseInt(this.getValue(), 10);
							e && 1 != e ? t.setAttribute("rowSpan", this.getValue()) : t.removeAttribute("rowSpan")
						}
					}, {
						type: "text",
						id: "colSpan",
						label: l.colSpan,
						default: "",
						validate: r.integer(l.invalidColSpan),
						setup: e(function(t) {
							if((t = parseInt(t.getAttribute("colSpan"), 10)) && 1 != t) return t
						}),
						commit: function(t) {
							var e = parseInt(this.getValue(), 10);
							e && 1 != e ? t.setAttribute("colSpan", this.getValue()) : t.removeAttribute("colSpan")
						}
					}, s, {
						type: "hbox",
						padding: 0,
						widths: ["60%", "40%"],
						children: [{
							type: "text",
							id: "bgColor",
							label: l.bgColor,
							default: "",
							setup: e(function(t) {
								var e = t.getAttribute("bgColor");
								return t.getStyle("background-color") || e
							}),
							commit: function(t) {
								this.getValue() ? t.setStyle("background-color", this.getValue()) : t.removeStyle("background-color"), t.removeAttribute("bgColor")
							}
						}, g ? {
							type: "button",
							id: "bgColorChoose",
							class: "colorChooser",
							label: l.chooseColor,
							onLoad: function() {
								this.getElement().getParent().setStyle("vertical-align", "bottom")
							},
							onClick: function() {
								t.getColorFromDialog(function(t) {
									t && this.getDialog().getContentElement("info", "bgColor").setValue(t), this.focus()
								}, this)
							}
						} : s]
					}, s, {
						type: "hbox",
						padding: 0,
						widths: ["60%", "40%"],
						children: [{
							type: "text",
							id: "borderColor",
							label: l.borderColor,
							default: "",
							setup: e(function(t) {
								var e = t.getAttribute("borderColor");
								return t.getStyle("border-color") || e
							}),
							commit: function(t) {
								this.getValue() ? t.setStyle("border-color", this.getValue()) : t.removeStyle("border-color"), t.removeAttribute("borderColor")
							}
						}, g ? {
							type: "button",
							id: "borderColorChoose",
							class: "colorChooser",
							label: l.chooseColor,
							style: (u ? "margin-right" : "margin-left") + ": 10px",
							onLoad: function() {
								this.getElement().getParent().setStyle("vertical-align", "bottom")
							},
							onClick: function() {
								t.getColorFromDialog(function(t) {
									t && this.getDialog().getContentElement("info", "borderColor").setValue(t), this.focus()
								}, this)
							}
						} : s]
					}]
				}]
			}]
		}],
		onShow: function() {
			this.cells = CKEDITOR.plugins.tabletools.getSelectedCells(this._.editor.getSelection()), this.setupContent(this.cells)
		},
		onOk: function() {
			for(var t = this._.editor.getSelection(), e = t.createBookmarks(), i = this.cells, o = 0; o < i.length; o++) this.commitContent(i[o]);
			this._.editor.forceNextSelectionCheck(), t.selectBookmarks(e), this._.editor.selectionChange()
		},
		onLoad: function() {
			var i = {};
			this.foreach(function(e) {
				e.setup && e.commit && (e.setup = CKEDITOR.tools.override(e.setup, function(t) {
					return function() {
						t.apply(this, arguments), i[e.id] = e.getValue()
					}
				}), e.commit = CKEDITOR.tools.override(e.commit, function(t) {
					return function() {
						i[e.id] !== e.getValue() && t.apply(this, arguments)
					}
				}))
			})
		}
	}
});