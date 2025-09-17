function getCookieWcag(name) {
    //
    const re = new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)');
    const match = document.cookie.match(re);
    return match ? decodeURIComponent(match[1]) : null;
    //
}

// tryb adhd
if (getCookieWcag('wcagc') === '2') {
    //
    const stripeH = 120;

    const topMask = document.createElement('div');
    topMask.id = 'overlay-top';
    topMask.className = 'overlay';
    document.body.appendChild(topMask);

    const bottomMask = document.createElement('div');
    bottomMask.id = 'overlay-bottom';
    bottomMask.className = 'overlay';
    document.body.appendChild(bottomMask);

    const adhdGrabber = document.createElement('div');
    adhdGrabber.id = 'adhd-bar-grabber';
    adhdGrabber.style.height = stripeH + 'px';
    adhdGrabber.style.top = '0px'; // tymczasowo
    document.body.appendChild(adhdGrabber);

    function moveStripe(posY) {
        const yStart = Math.max(0, posY - stripeH / 2);
        const yEnd   = Math.min(window.innerHeight, yStart + stripeH);

        topMask.style.height = yStart + 'px';
        bottomMask.style.top = yEnd + 'px';
        bottomMask.style.height = (window.innerHeight - yEnd) + 'px';
        adhdGrabber.style.top = yStart + 'px';
    }

    moveStripe(window.innerHeight / 2);

    document.addEventListener('mousemove', e => {
        moveStripe(e.clientY);
    });

    let movingAdhdStripe = false;

    adhdGrabber.addEventListener('touchstart', function(e) {
        movingAdhdStripe = true;
    }, { passive: false });

    adhdGrabber.addEventListener('touchmove', function(e) {
        if (!movingAdhdStripe) return;
        if (!e.touches || !e.touches.length) return;
        e.preventDefault();
        const touch = e.touches[0];
        moveStripe(touch.clientY);
    }, { passive: false });

    document.addEventListener('touchend', function(e) {
        movingAdhdStripe = false;
    }, { passive: false });

    window.addEventListener('resize', () => {
        moveStripe(window.innerHeight / 2);
    });

}

// wylaczenie animacji
if (getCookieWcag('wcaga') === '1') {
    //
    document.documentElement.classList.add('reduce-motion');
    //
    jQuery(function($){
      $('.slick-slider').each(function(){
        $(this).slick('slickPause');
      });
    });   
    //
}

if (getCookieWcag('wcagle') === '1') {

    function wrapInlineBlocks(container) {
      let node = container.firstChild;
      let wrapper = null;

      while (node) {
        const next = node.nextSibling;

        if (
          node.nodeType === 1 &&
          (node.tagName === 'BR' || /^H[1-6]$/.test(node.tagName))
        ) {
          wrapper = null;
          if (node.tagName === 'BR') {
            node.remove();
          }
        }

        else if (node.nodeType === 3 && !node.nodeValue.trim()) {
          node.remove();
        }

        else {
          if (!wrapper) {

            wrapper = document.createElement('div');
            wrapper.className = 'CzytanieHover';
            container.insertBefore(wrapper, node);
          }

          wrapper.appendChild(node);
        }

        node = next;
      }
    }

    document.querySelectorAll('.FormatEdytor > div:not(.CzytanieHover)').forEach(wrapInlineBlocks);

    (function(){
      
      if (!('speechSynthesis' in window)) return;

      const synth = window.speechSynthesis;
      let voices = [], selectedVoice = null;

      function loadVoices() {
        voices = synth.getVoices();
        selectedVoice =
          voices.find(v => v.lang.startsWith('pl')) ||
          voices.find(v => /polski/i.test(v.name)) ||
          voices[0];
      }
      synth.onvoiceschanged = loadVoices;
      loadVoices();

      function speakText(txt, readEl) {
        if (readEl && readEl.tagName !== 'A') {
          readEl._prevCursor = readEl.style.cursor;
          readEl.style.cursor = 'help';
        }
        if (!selectedVoice) return;
        const u = new SpeechSynthesisUtterance(txt);
        u.voice = selectedVoice;
        u.lang  = selectedVoice?.lang || 'pl-PL';
        synth.cancel();
        synth.speak(u);
      }

      function handleMouseOver(e) {
        let el = e.target; 

        // specjalnie dla IMG: czytaj tylko alt/aria-label/title!
        if (el.tagName === 'IMG') {
          let txt = el.getAttribute('aria-label')?.trim() || el.alt?.trim() || el.title?.trim();
          if (!txt) return;
          speakText(txt, el);
          return;
        }

        const link = el.closest('a');
        if (link) el = link;

        const allowed = 'p,li,h1,h2,h3,h4,h5,h6,a,button,td,span,em,div,summary,small,strong,b,label,input,select';
        if (!el.matches(allowed) && !el.closest(allowed)) return;
        let readEl = el.matches(allowed) ? el : el.closest(allowed);

        const st = getComputedStyle(readEl);
        if (st.display === 'none' || st.visibility === 'hidden' || +st.opacity === 0) return;

        // SELECT tylko jesli aria-label
        if (readEl.tagName === 'SELECT') {
          let label = readEl.getAttribute('aria-label');
          if (!label || !label.trim()) return;
        }

        // odrzuc elementy bez tekstu (poza a, label, input, select, img)
        if (
          readEl.tagName !== 'A' &&
          readEl.tagName !== 'LABEL' &&
          readEl.tagName !== 'INPUT' &&
          readEl.tagName !== 'SELECT' &&
          readEl.tagName !== 'IMG'
        ) {
          if (!readEl.getAttribute('aria-label')) {
            const hasTextNode = Array.from(readEl.childNodes).some(n =>
              n.nodeType === Node.TEXT_NODE && n.nodeValue.trim().length > 0
            );
            if (!hasTextNode) return;
          }
        }

        // pobierz tekst: aria-label > data-hover > input/textarea/button value > IMG alt > textContent > title
        let txt =
          readEl.getAttribute('aria-label')?.trim()
          || readEl.getAttribute('data-hover')?.trim()
          || (readEl.matches('input,textarea,button') && (readEl.value || readEl.textContent)?.trim())
          || (readEl.tagName === 'IMG' && (readEl.alt || '').trim())
          || readEl.textContent.trim()
          || (readEl.title || '').trim();

        if (!txt) return;

        // zmien kursor (tylko nie dla <a>)
        if (readEl.tagName !== 'A') {
          readEl._prevCursor = readEl.style.cursor;
          readEl.style.cursor = 'help';
        }

        speakText(txt, readEl);
      }

      function handleMouseOut(e) {
        const el = e.target;
        if (el._prevCursor !== undefined) {
          el.style.cursor = el._prevCursor;
          delete el._prevCursor;
        }
        synth.cancel();
      }

      document.body.addEventListener('mouseover', handleMouseOver);
      document.body.addEventListener('mouseout',  handleMouseOut);
      
      document.body.addEventListener('touchstart', handleMouseOver, {passive: true});
      document.body.addEventListener('touchend', handleMouseOut, {passive: true});
      
      document.body.addEventListener('click', handleMouseOver);
      document.body.addEventListener('click', handleMouseOut);

    })();

}

$(document).on('keydown', function(e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
        $('.PasekDostepnosci').hide();
        usunCookie('wcag');
    }
}); 
function WlaczOknoDostepnosci() {
    //
    $('.PasekDostepnosci').show();
    ustawCookie('wcag','1',30);
    //   
}
function WylaczOknoDostepnosci() {
    //
    $('.PasekDostepnosci').hide();
    usunCookie('wcag');
    //
}

function ResetOknoDostepnosci() {
    //
    const allCookies = document.cookie.split('; ');
    //
    allCookies.forEach(cookie => {
      const [name] = cookie.split('=');  
      if (name.startsWith('wca')) { 
        document.cookie = name + '=; expires=' + new Date(0).toUTCString() + '; path=/';  
      }
    });    
    //
    ustawCookie('wcag','1',30);
    window.location.reload();
    //
}

function KontrastSvgTlo() {
    //
    $('*').each(function () {
      //
      const el = this;
      const $el = $(el);
      //
      if (
        $el.closest('.PortaleSpolecznoscioweIkony').length > 0 ||
        $el.is('#KlasaEnergetyczna') ||
        $el.closest('#KlasaEnergetyczna').length > 0 ||
        $el.hasClass('DoKoszyka') ||
        $el.hasClass('przycisk') ||
        $el.hasClass('DoKoszykaKartaProduktu')
      ) {
        return;
      }
      //
      const bg = getComputedStyle(el).getPropertyValue('background-image');
      let beforeBg = '';
      try {
        beforeBg = getComputedStyle(el, '::before').getPropertyValue('background-image');
      } catch (e) {}

      const hasSvg = (bg && bg.includes('.svg')) || (beforeBg && beforeBg.includes('.svg'));

      if (hasSvg) {
          //
          if (
            !$el.hasClass('OdwroconyInvert') &&
            $el.parents('.OdwroconyInvert').length === 0
          ) {
            $el.addClass('OdwroconyInvert OdwroconyFiltr');
          }
          //
      } else {
          //
          if ($el.hasClass('OdwroconyInvert') && $el.parents('.OdwroconyInvert').length === 0) {
            $el.removeClass('OdwroconyInvert OdwroconyFiltr');
          }
          //
      }
      //
    });
    //
}

function UstawKontrast() {
    //
    ustawCookie('wcagk','1',30);  
    $('body').addClass('Kontrast');
    //
    $(function () {
        //
        if ($('body').hasClass('Kontrast')) {
            KontrastSvgTlo();
        }
        //
    });
    //
    let resizeTimer;
    //
    $(window).on('resize', function () {
        //
        if ($('body').hasClass('Kontrast')) {
          clearTimeout(resizeTimer);

          resizeTimer = setTimeout(function () {
            KontrastSvgTlo(); 
          }, 300); 
        }
        //
    });
    
}
    
function zmianaKontrastu(kontrast) {
  if ( parseInt(kontrast) == 0 ) {
       //
       usunCookie('wcagk');
       window.location.reload();
       //
  }
  if ( parseInt(kontrast) == 1 ) {
       //
       ustawCookie('wcagk','1',30);
       window.location.reload();
       //
  }
}

function rozmiarFont(rozmiar) {
    //
    ustawCookie('wcagf',rozmiar,30);
    window.location.reload();
    //
}

function rozmiarInterlinia(rozmiar) {
    //
    ustawCookie('wcagl',rozmiar,30);
    window.location.reload();
    //
}

function rozmiarOdstepliter(rozmiar) {
    //
    ustawCookie('wcago',rozmiar,30);
    window.location.reload();
    //
}

function rozmiarKursor(rozmiar) {
    //
    ustawCookie('wcagc',rozmiar,30);
    window.location.reload();
    //
}

function zmianaSzarosci(tryb) {
    //
    ustawCookie('wcags',tryb,30);
    window.location.reload();
    //
}

function zmianaObrazki(tryb) {
    //
    ustawCookie('wcagi',tryb,30);
    window.location.reload();
    //
}

function zmianaRodzajuCzcionki(tryb) {
    //
    ustawCookie('wcagcz',tryb,30);
    window.location.reload();
    //
}

function zmianaAnimacji(tryb) {
    //
    ustawCookie('wcaga',tryb,30);
    window.location.reload();
    //
}

function zmianaWyrownanieTekstu(tryb) {
    //
    ustawCookie('wcagw',tryb,30);
    window.location.reload();
    //
}

function zmianaNasycenie(tryb) {
    //
    ustawCookie('wcagn',tryb,30);
    window.location.reload();
    //
}

function zmianaOdnosniki(tryb) {
    //
    ustawCookie('wcagod',tryb,30);
    window.location.reload();
    //
}

function zmianaCzytnikEkranu(tryb) {
    //
    ustawCookie('wcagle',tryb,30);
    window.location.reload();
    //
}

// restet funkcji
function aktualizujStickyMenu() {
    //
    return
    //
}

// sprawdzanie polozenia ikonki dostepnosci
$(function(){
  
    var $icon     = $('.IkonaDostepnosci');
    var iconEl    = $icon[0];

    var origBottomRaw = window.getComputedStyle(iconEl).getPropertyValue('bottom');
    var origBottom    = parseFloat(origBottomRaw) || 0;
    var offset        = 60;  // o ile px podnosimy ikonę

    function shouldLiftIcon(){
      
        var sticky = document.querySelector('.MenuSticky') !== null;

        var header = document.querySelector('.MenuStickyAnimacja');
        if (!header) return false;
        var bottom = window.getComputedStyle(header).getPropertyValue('bottom');
        return sticky && bottom.trim() === '0px';
      
    }

    function adjustIcon(){
      
        if ( shouldLiftIcon() ){
          
            $icon.css({
              bottom: (origBottom + offset) + 'px',
            });
          
        } else {
          
            $icon.css({
              bottom: origBottom + 'px',
              zIndex: ''
            });
          
        }
      
    }

    var ticking = false;
    
    function onScrollOrResize(){
      
        if (!ticking){
          
            window.requestAnimationFrame(function(){
              adjustIcon();
              ticking = false;
            });
            ticking = true;
            
        }
      
    }

    var headerEl = document.querySelector('.MenuStickyAnimacja');
    
    if (headerEl){
      new MutationObserver(onScrollOrResize)
        .observe(headerEl, { attributes: true, attributeFilter: ['class'] });
    }

    adjustIcon();
    $(window).on('scroll resize', onScrollOrResize);
    
});

/* przywrocenie oryginalnych kolorow dla klasy energetycznej */

(function(){

  setTimeout(() => {

    if (!document.body.classList.contains('Kontrast')) {
      return;
    }

    if (!document.querySelector('.KlasaEnergetyczna')) {
      return;
    }

    const items = Array.from(document.querySelectorAll('[class*="KlasaEnergetyczna-"]'));

    document.body.classList.remove('Kontrast');

    requestAnimationFrame(() => {
      items.forEach(el => {
        const bg = getComputedStyle(el).backgroundColor;
        el.style.setProperty('background-color',  bg, 'important');
        el.style.setProperty('border-left-color', bg, 'important');
      });

      document.body.classList.add('Kontrast');
    });

  }, 500);
})();


