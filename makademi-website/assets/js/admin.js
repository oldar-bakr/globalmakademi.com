(function () {
  'use strict';

  function csrfToken() {
    var input = document.querySelector('input[name="_csrf"]');
    return input ? input.value : '';
  }

  function directChildren(container, selector) {
    return Array.prototype.filter.call(container.children, function (el) {
      return el.matches(selector);
    });
  }

  function initReorder(container) {
    var url = container.dataset.reorderUrl;
    if (!url) return;
    var itemSelector = container.dataset.reorderItem || '[data-id]';
    var handleSelector = container.dataset.reorderHandle || '.drag-handle';
    var scope = container.dataset.reorderScope || '';

    var dragging = null;
    var pointerId = null;
    var capturedHandle = null;
    var lastSavedOrder = currentOrder();

    function currentOrder() {
      return directChildren(container, itemSelector)
        .map(function (el) { return el.dataset.id; })
        .filter(Boolean)
        .join(',');
    }

    function onPointerDown(e) {
      if (e.button !== undefined && e.button !== 0) return;
      var handle = e.target.closest(handleSelector);
      if (!handle || !container.contains(handle)) return;
      var item = handle.closest(itemSelector);
      if (!item || item.parentNode !== container) return;
      e.preventDefault();
      dragging = item;
      pointerId = e.pointerId;
      capturedHandle = handle;
      try { handle.setPointerCapture(pointerId); } catch (_) {}
      item.classList.add('is-dragging');
      container.classList.add('is-reordering');
      handle.addEventListener('pointermove', onPointerMove);
      handle.addEventListener('pointerup', onPointerUp);
      handle.addEventListener('pointercancel', onPointerUp);
    }

    function onPointerMove(e) {
      if (!dragging) return;
      e.preventDefault();
      // Find the item under the pointer that is a sibling we can swap with.
      var nodes = document.elementsFromPoint
        ? document.elementsFromPoint(e.clientX, e.clientY)
        : [document.elementFromPoint(e.clientX, e.clientY)];
      var sibling = null;
      for (var i = 0; i < nodes.length; i++) {
        if (!nodes[i]) continue;
        var match = nodes[i].closest(itemSelector);
        if (match && match !== dragging && match.parentNode === container) {
          sibling = match;
          break;
        }
      }
      if (!sibling) return;
      var rect = sibling.getBoundingClientRect();
      var horizontal = rect.width > 0 && container.classList.contains('reorder-horizontal');
      var midpoint = horizontal
        ? (rect.left + rect.right) / 2
        : (rect.top + rect.bottom) / 2;
      var pointerCoord = horizontal ? e.clientX : e.clientY;
      if (pointerCoord < midpoint) {
        if (sibling.previousElementSibling !== dragging) {
          container.insertBefore(dragging, sibling);
        }
      } else {
        if (sibling.nextElementSibling !== dragging) {
          container.insertBefore(dragging, sibling.nextSibling);
        }
      }
    }

    function onPointerUp() {
      if (!dragging) return;
      var item = dragging;
      var handle = capturedHandle;
      try { handle.releasePointerCapture(pointerId); } catch (_) {}
      handle.removeEventListener('pointermove', onPointerMove);
      handle.removeEventListener('pointerup', onPointerUp);
      handle.removeEventListener('pointercancel', onPointerUp);
      item.classList.remove('is-dragging');
      container.classList.remove('is-reordering');
      dragging = null;
      capturedHandle = null;

      var newOrder = currentOrder();
      if (newOrder === lastSavedOrder) return;
      saveOrder(newOrder);
    }

    function saveOrder(newOrder) {
      var ids = newOrder.split(',').filter(Boolean);
      var fd = new FormData();
      fd.append('_csrf', csrfToken());
      fd.append('do', 'reorder');
      if (scope) fd.append('scope_id', scope);
      ids.forEach(function (id) { fd.append('order[]', id); });
      container.classList.add('is-saving');
      fetch(url, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'fetch', 'Accept': 'application/json' }
      }).then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      }).then(function (data) {
        if (!data || !data.ok) throw new Error('Server rejected reorder');
        lastSavedOrder = newOrder;
        container.classList.remove('is-saving');
        container.classList.add('is-saved');
        updateRankLabels();
        setTimeout(function () { container.classList.remove('is-saved'); }, 900);
      }).catch(function (err) {
        container.classList.remove('is-saving');
        console.error('Reorder save failed:', err);
        window.alert('Could not save the new order. Please refresh the page and try again.');
      });
    }

    function updateRankLabels() {
      var i = 0;
      directChildren(container, itemSelector).forEach(function (el) {
        i += 1;
        var label = el.querySelector('[data-sort-label]');
        if (label) {
          var prefix = (label.textContent || '').match(/^[^\d]*/);
          label.textContent = (prefix ? prefix[0] : '') + i;
        }
      });
    }

    container.addEventListener('pointerdown', onPointerDown);
  }

  function init() {
    document.querySelectorAll('[data-reorder]').forEach(initReorder);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
