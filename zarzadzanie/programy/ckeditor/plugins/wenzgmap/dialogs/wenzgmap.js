/*
 MIT

 Creato by Webz Ray
*/
CKEDITOR.dialog.add("wenzgmapDialog", function (b) {
    return {
        title: "Wstaw mapę Google",
        minWidth: 400,
        minHeight: 75,
        contents: [
            {
                id: "tab-basic",
                label: "Basic Settings",
                elements: [
                    { type: "text", id: "addressStr", label: "Wprowadź adres jaki ma być wyświetlany na mapie <br> np. Firma X, ul.Jakaś 11, 00-001 Miasto" },
                    { type: "text", id: "mapWidth", label: "Szerokość mapy (px lub %)", style: "width:50%;", default: '100' },
                    { type: 'select',
                      id: 'mapWidthJm',
                      label: 'Jednostka szerokości',
                      items: [
                        ['piksel', "px"],
                        ['procent', "%"]
                      ],
                      default: '%'                      
                    },    
                    { type: "text", id: "mapHeight", label: "Wysokość mapy (px)", style: "width:50%;", default: '200' },
                ],
            },
        ],
        onOk: function () {
            var c = this.getValueOf("tab-basic", "addressStr").trim(),
                d = this.getValueOf("tab-basic", "mapWidth").trim(),
                dp = this.getValueOf("tab-basic", "mapWidthJm").trim(),
                e = this.getValueOf("tab-basic", "mapHeight").trim(),
                a = b.document.createElement("iframe");         
            a.setAttribute("width", d);
            a.setAttribute("height", e);
            a.setStyle( 'width', d + dp );
            a.setAttribute("src", "//maps.google.com/maps?q=" + c + "&num=1&t=m&ie=UTF8&z=14&output=embed");
            a.setAttribute("frameborder", "0");
            a.setAttribute("scrolling", "no");
            b.insertElement(a);
        },
    };
});
