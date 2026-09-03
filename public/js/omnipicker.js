/**
 * OmniPicker - Visual DOM Selector for OmniScrape AI
 * Injected into the proxy iframe to allow visual element selection.
 */

(function() {
    console.log("OmniPicker: Initializing Visual Selector...");

    // Create highlight overlay
    const overlay = document.createElement('div');
    overlay.style.position = 'absolute';
    overlay.style.pointerEvents = 'none';
    overlay.style.zIndex = '9999999';
    overlay.style.border = '2px solid #00b4d8';
    overlay.style.backgroundColor = 'rgba(0, 180, 216, 0.2)';
    overlay.style.transition = 'all 0.1s ease-out';
    overlay.style.display = 'none';
    document.body.appendChild(overlay);

    let currentTarget = null;
    let isActive = true;

    // Generate unique CSS selector for an element
    function generateSelector(el) {
        if (!el || el.tagName.toLowerCase() === 'body') return 'body';
        
        let path = [];
        while (el && el.nodeType === Node.ELEMENT_NODE) {
            let selector = el.nodeName.toLowerCase();
            
            // Prefer ID
            if (el.id) {
                selector += '#' + el.id;
                path.unshift(selector);
                break; // IDs are unique, we can stop here
            } else {
                // If classes exist, use them
                let sibling = el, nth = 1;
                while (sibling = sibling.previousElementSibling) {
                    if (sibling.nodeName.toLowerCase() == selector)
                       nth++;
                }
                
                if (nth != 1) {
                    selector += `:nth-of-type(${nth})`;
                } else if (el.className && typeof el.className === 'string') {
                    // Try to use a class if it looks decent (not dynamic noise)
                    const classes = el.className.split(/\s+/).filter(c => c && !c.includes('hover') && !c.includes('active') && !/^[a-zA-Z0-9]{8,}$/.test(c));
                    if (classes.length > 0) {
                        selector += '.' + classes[0];
                    }
                }
            }
            path.unshift(selector);
            el = el.parentNode;
        }
        return path.join(' > ');
    }

    document.addEventListener('mouseover', (e) => {
        if (!isActive) return;
        
        const el = e.target;
        if (el === overlay || el === document.body) return;
        
        currentTarget = el;
        
        const rect = el.getBoundingClientRect();
        
        overlay.style.display = 'block';
        overlay.style.top = (rect.top + window.scrollY) + 'px';
        overlay.style.left = (rect.left + window.scrollX) + 'px';
        overlay.style.width = rect.width + 'px';
        overlay.style.height = rect.height + 'px';
    });

    document.addEventListener('mouseout', (e) => {
        if (!isActive) return;
        overlay.style.display = 'none';
        currentTarget = null;
    });

    document.addEventListener('click', (e) => {
        if (!isActive) return;
        e.preventDefault();
        e.stopPropagation();

        if (currentTarget) {
            const selector = generateSelector(currentTarget);
            const textContent = currentTarget.innerText.trim().substring(0, 50);
            
            // Flash effect
            overlay.style.backgroundColor = 'rgba(114, 9, 183, 0.5)';
            overlay.style.borderColor = '#7209b7';
            
            setTimeout(() => {
                overlay.style.backgroundColor = 'rgba(0, 180, 216, 0.2)';
                overlay.style.borderColor = '#00b4d8';
            }, 300);

            // Send message to parent window (Dashboard)
            window.parent.postMessage({
                type: 'OMNIPICKER_SELECTION',
                selector: selector,
                text: textContent,
                tag: currentTarget.tagName.toLowerCase()
            }, '*');
        }
    }, true);

    // Listen for commands from parent
    window.addEventListener('message', (e) => {
        if (e.data && e.data.type === 'OMNIPICKER_TOGGLE') {
            isActive = e.data.active;
            if (!isActive) overlay.style.display = 'none';
        }
    });
})();
