/** Image previews and local cropping. Existing upload handlers own all requests. */
(function (window, document) {
    'use strict';
    if (window.TerminalImages) return;
    var crop = null, viewer = null, accepted = new WeakMap(), replaying = new WeakSet();
    var previewSelector = 'img[data-vehicle-custom-photo], img[data-vt-photo], img.vehicle-thumb-img, img[data-image-preview], ' +
        '#reg_veh_photo_preview, #edit_veh_photo_preview, #reg_veh_type_fallback img, #edit_veh_type_fallback img, ' +
        'img[id^="edit_vt_photo_preview_"], img[id^="edit_vt_preview_photo_"], #add_vt_photo_preview, #add_vt_preview_photo, ' +
        '#editUserAvatarPreview :is(img,svg), #createUserAvatarPreview :is(img,svg), #modalAvatarPreviewCircle :is(img,svg)';
    var canDialog = typeof window.HTMLDialogElement === 'function' && typeof window.HTMLDialogElement.prototype.showModal === 'function';

    function clamp(value, min, max) { return Math.max(min, Math.min(max, value)); }
    function makeDialog(className, title, contents) {
        var dialog = document.createElement('dialog');
        dialog.className = 'tq-image-dialog ' + className;
        dialog.innerHTML = '<div class="tq-image-heading"><h2 id="' + className + '-title"></h2><button type="button" class="tq-image-close" aria-label="Close">&times;</button></div>' + contents;
        dialog.querySelector('h2').textContent = title;
        dialog.setAttribute('aria-labelledby', className + '-title');
        document.body.appendChild(dialog);
        return dialog;
    }
    function restoreFocus(element) {
        if (element && element.isConnected && element.getClientRects().length) element.focus({preventScroll:true});
    }

    function buildCropper() {
        var dialog = makeDialog('tq-image-cropper', 'Crop image',
            '<p class="tq-image-help" id="tq-crop-help">Drag the crop area to move it. Drag the lower-right corner to resize. Arrow keys move it; Shift + arrow keys resize it.</p>' +
            '<div class="tq-image-toolbar"><label for="tq-crop-ratio">Crop shape</label><select id="tq-crop-ratio" data-no-autocomplete><option value="free">Free crop</option><option value="original">Original ratio</option><option value="1">1:1 · Square</option><option value="1.3333333333">4:3 · Landscape</option><option value="1.7777777778">16:9 · Wide</option><option value=".75">3:4 · Portrait</option><option value=".5625">9:16 · Tall</option></select><button type="button" class="tq-image-action" data-reset>Reset</button></div>' +
            '<div class="tq-crop-stage"><div class="tq-crop-image-wrap"><img class="tq-crop-image" alt="Image to crop" draggable="false"><div class="tq-crop-selection" tabindex="0" role="group" aria-label="Crop area" aria-describedby="tq-crop-help"><button type="button" class="tq-crop-handle" aria-label="Resize crop area"></button></div></div></div>' +
            '<p class="tq-image-message" role="status"></p><div class="tq-image-actions"><button type="button" class="tq-image-action" data-cancel>Cancel</button><button type="button" class="tq-image-action" data-original>Use original</button><button type="button" class="tq-image-action tq-image-primary" data-apply>Use crop</button></div>');
        var image = dialog.querySelector('img'), wrap = dialog.querySelector('.tq-crop-image-wrap');
        var box = dialog.querySelector('.tq-crop-selection'), ratio = dialog.querySelector('select');
        var message = dialog.querySelector('[role="status"]'), apply = dialog.querySelector('[data-apply]');
        var state = null, gesture = null;

        function paint() {
            if (!state) return;
            var r = state.rect;
            box.style.left = (r.x / image.naturalWidth * 100) + '%';
            box.style.top = (r.y / image.naturalHeight * 100) + '%';
            box.style.width = (r.w / image.naturalWidth * 100) + '%';
            box.style.height = (r.h / image.naturalHeight * 100) + '%';
            box.setAttribute('aria-valuetext', Math.round(r.w) + ' by ' + Math.round(r.h) + ' pixels');
            message.textContent = Math.round(r.w) + ' × ' + Math.round(r.h) + ' px selected' + (state.file.type === 'image/gif' ? '. Cropping saves a still image; Use original keeps GIF animation.' : '');
            message.removeAttribute('data-error');
        }
        function selectedRatio() {
            return ratio.value === 'free' ? 0 : ratio.value === 'original' ? image.naturalWidth / image.naturalHeight : Number(ratio.value);
        }
        function reset() {
            if (!state || !image.naturalWidth) return;
            var w = image.naturalWidth, h = image.naturalHeight, shape = selectedRatio();
            if (shape && w / h > shape) w = h * shape;
            else if (shape) h = w / shape;
            state.rect = {x:(image.naturalWidth-w)/2, y:(image.naturalHeight-h)/2, w:w, h:h};
            paint();
        }
        function resize(r, dx, dy) {
            var maxW = image.naturalWidth - r.x, maxH = image.naturalHeight - r.y;
            var minW = Math.min(16, maxW), minH = Math.min(16, maxH), shape = selectedRatio();
            var w = clamp(r.w + dx, minW, maxW), h = clamp(r.h + dy, minH, maxH);
            if (shape) {
                w = Math.abs(dx) >= Math.abs(dy * shape) ? w : h * shape;
                w = clamp(w, Math.min(16, maxW, maxH * shape), Math.min(maxW, maxH * shape));
                h = w / shape;
            }
            return {x:r.x, y:r.y, w:w, h:h};
        }
        box.addEventListener('pointerdown', function (event) {
            if (!state || !image.naturalWidth || event.button !== 0) return;
            event.preventDefault();
            var bounds = wrap.getBoundingClientRect();
            gesture = {id:event.pointerId, x:event.clientX, y:event.clientY, sx:image.naturalWidth/bounds.width, sy:image.naturalHeight/bounds.height,
                resize:!!event.target.closest('.tq-crop-handle'), rect:Object.assign({}, state.rect)};
            box.setPointerCapture(event.pointerId);
        });
        box.addEventListener('pointermove', function (event) {
            if (!gesture || gesture.id !== event.pointerId || !state) return;
            var dx = (event.clientX-gesture.x)*gesture.sx, dy = (event.clientY-gesture.y)*gesture.sy, r = gesture.rect;
            state.rect = gesture.resize ? resize(r,dx,dy) : {x:clamp(r.x+dx,0,image.naturalWidth-r.w), y:clamp(r.y+dy,0,image.naturalHeight-r.h), w:r.w, h:r.h};
            paint();
        });
        ['pointerup','pointercancel','lostpointercapture'].forEach(function (name) { box.addEventListener(name,function () { gesture=null; }); });
        box.addEventListener('keydown', function (event) {
            if (!state || !['ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(event.key)) return;
            event.preventDefault();
            var step = Math.max(1, Math.round(Math.min(image.naturalWidth,image.naturalHeight)/100));
            var dx = event.key === 'ArrowLeft' ? -step : event.key === 'ArrowRight' ? step : 0;
            var dy = event.key === 'ArrowUp' ? -step : event.key === 'ArrowDown' ? step : 0;
            var r = state.rect;
            state.rect = event.shiftKey ? resize(r,dx,dy) : {x:clamp(r.x+dx,0,image.naturalWidth-r.w), y:clamp(r.y+dy,0,image.naturalHeight-r.h), w:r.w, h:r.h};
            paint();
        });
        function finish(file) {
            if (!state) return;
            var current = state;
            state = null; gesture = null;
            dialog.close(); image.removeAttribute('src'); URL.revokeObjectURL(current.url);
            apply.disabled = false;
            restoreFocus(current.focus);
            current.resolve(file);
        }
        ratio.addEventListener('change', reset);
        dialog.querySelector('[data-reset]').addEventListener('click',reset);
        dialog.querySelector('[data-original]').addEventListener('click',function () { finish(state && state.file); });
        dialog.querySelector('[data-cancel]').addEventListener('click',function () { finish(null); });
        dialog.querySelector('.tq-image-close').addEventListener('click',function () { finish(null); });
        dialog.addEventListener('cancel',function (event) { event.preventDefault(); finish(null); });
        dialog.addEventListener('click',function (event) {
            if (event.target !== dialog) return;
            var r=dialog.getBoundingClientRect();
            if(event.clientX<r.left || event.clientX>r.right || event.clientY<r.top || event.clientY>r.bottom) finish(null);
        });
        image.addEventListener('load',function () { if (state) { apply.disabled=false; reset(); } });
        image.addEventListener('error',function () {
            if (!state) return;
            apply.disabled=true; message.textContent='This image could not be opened. Choose another image or use the original file.'; message.setAttribute('data-error','');
        });
        apply.addEventListener('click',function () {
            if (!state || apply.disabled) return;
            var current=state, r=current.rect, canvas=document.createElement('canvas');
            // Export once, bounded to 2048 px. Moving the selection never redraws a canvas.
            var scale=Math.min(1,2048/Math.max(r.w,r.h));
            canvas.width=Math.max(1,Math.round(r.w*scale)); canvas.height=Math.max(1,Math.round(r.h*scale));
            var context=canvas.getContext('2d');
            if (!context) return;
            context.drawImage(image,r.x,r.y,r.w,r.h,0,0,canvas.width,canvas.height);
            var type=current.file.type === 'image/jpeg' || current.file.type === 'image/webp' ? current.file.type : 'image/png';
            apply.disabled=true; message.textContent='Preparing cropped image…';
            canvas.toBlob(function (blob) {
                canvas.width=canvas.height=0;
                if (state !== current) return;
                if (!blob) { apply.disabled=false; message.textContent='The crop could not be saved. Try again or use the original.'; message.setAttribute('data-error',''); return; }
                var extension=blob.type === 'image/jpeg' ? 'jpg' : blob.type === 'image/webp' ? 'webp' : 'png';
                finish(new File([blob],current.file.name.replace(/\.[^.]+$/,'') + '-cropped.' + extension,{type:blob.type,lastModified:Date.now()}));
            },type,.9);
        });
        return {
            dialog:dialog,
            open:function (file,input) {
                return new Promise(function (resolve) {
                    if(state) finish(null);
                    state={file:file,url:URL.createObjectURL(file),focus:document.activeElement,resolve:resolve,rect:null};
                    ratio.value=input.dataset.cropRatio || (/avatar/i.test(input.id+' '+input.name) ? '1' : 'original');
                    if(ratio.selectedIndex<0) ratio.value='original';
                    apply.disabled=true; message.textContent='Opening image…'; message.removeAttribute('data-error');
                    dialog.showModal(); image.src=state.url; ratio.focus({preventScroll:true});
                });
            },
            active:function () { return !!state; },
            cancel:function () { finish(null); }
        };
    }

    function setFiles(input,files) {
        var transfer=new DataTransfer(); files.forEach(function (file) { transfer.items.add(file); }); input.files=transfer.files;
    }
    function canCrop(input,file) {
        return input instanceof HTMLInputElement && input.type==='file' && !input.multiple && !input.hasAttribute('data-no-image-crop') &&
            file && /^image\/(png|jpe?g|webp|gif)$/i.test(file.type) && canDialog && typeof DataTransfer==='function';
    }
    function cropChange(event) {
        var input=event.target;
        if (!(input instanceof HTMLInputElement) || replaying.has(input)) return;
        var file=input.files && input.files[0];
        if (!canCrop(input,file)) return;
        event.stopImmediatePropagation();
        var previous=input.value ? accepted.get(input) || [] : [];
        if(!crop) crop=buildCropper();
        crop.open(file,input).then(function (result) {
            if (!input.isConnected) return;
            if (!result) { setFiles(input,previous); return; }
            setFiles(input,[result]); accepted.set(input,[result]);
            replaying.add(input);
            try { input.dispatchEvent(new Event('change',{bubbles:true})); }
            finally { replaying.delete(input); }
            if (!input.files.length) accepted.delete(input); // Existing instant upload handlers may clear the picker.
        });
    }

    function buildViewer() {
        var dialog=document.createElement('dialog');
        dialog.className='tq-image-dialog tq-image-viewer';
        dialog.setAttribute('aria-label','Photo');
        dialog.innerHTML='<div class="tq-viewer-stage" tabindex="0" aria-label="Photo; use plus and minus to zoom, arrow keys to move"><img class="tq-viewer-photo" alt="" draggable="false"></div>' +
            '<button type="button" class="tq-viewer-control tq-viewer-close" aria-label="Close">&times;</button>' +
            '<div class="tq-viewer-controls"><button type="button" class="tq-viewer-control" data-out aria-label="Zoom out">−</button><button type="button" class="tq-viewer-control" data-in aria-label="Zoom in">+</button></div>' +
            '<p class="tq-viewer-message" role="status"></p>';
        document.body.appendChild(dialog);
        var image=dialog.querySelector('img'),stage=dialog.querySelector('.tq-viewer-stage'),status=dialog.querySelector('[role="status"]');
        var zoom=1,x=0,y=0,gesture=null,focus=null,points=new Map();
        function paint() {
            var bounds=stage.getBoundingClientRect();
            var fit=image.naturalWidth ? Math.min((bounds.width-24)/image.naturalWidth,(bounds.height-24)/image.naturalHeight) : 1;
            var width=image.naturalWidth*fit,height=image.naturalHeight*fit;
            image.style.width=width+'px';image.style.height=height+'px';
            var maxX=Math.max(0,(width*zoom-bounds.width)/2),maxY=Math.max(0,(height*zoom-bounds.height)/2);
            x=clamp(x,-maxX,maxX);y=clamp(y,-maxY,maxY);
            image.style.transform='translate('+x+'px,'+y+'px) scale('+zoom+')';
            stage.toggleAttribute('data-zoomed',zoom>1);
            dialog.querySelector('[data-out]').disabled=zoom<=1; dialog.querySelector('[data-in]').disabled=zoom>=4;
        }
        function changeZoom(value,clientX,clientY) {
            var previous=zoom;zoom=clamp(value,1,4);
            if(typeof clientX==='number') {
                var r=stage.getBoundingClientRect(),dx=clientX-r.left-r.width/2,dy=clientY-r.top-r.height/2;
                x=dx-(dx-x)*zoom/previous;y=dy-(dy-y)*zoom/previous;
            }
            paint();
        }
        function close() { dialog.close(); image.removeAttribute('src'); gesture=null;points.clear();restoreFocus(focus); }
        dialog.querySelector('.tq-viewer-close').addEventListener('click',close);
        dialog.addEventListener('cancel',function (event) {event.preventDefault();close();});
        stage.addEventListener('click',function (event) {if(event.target===stage && zoom===1) close();});
        dialog.querySelector('[data-in]').addEventListener('click',function () {changeZoom(zoom+.5);});
        dialog.querySelector('[data-out]').addEventListener('click',function () {changeZoom(zoom-.5);});
        stage.addEventListener('wheel',function (event) {event.preventDefault();changeZoom(zoom+(event.deltaY<0?.25:-.25),event.clientX,event.clientY);},{passive:false});
        function beginGesture() {
            var p=Array.from(points.values()),r=stage.getBoundingClientRect();
            if(!p.length) {gesture=null;return;}
            gesture={x:x,y:y,zoom:zoom,cx:p[0].x,cy:p[0].y};
            if(p.length>1) {
                gesture.distance=Math.hypot(p[1].x-p[0].x,p[1].y-p[0].y);
                gesture.cx=(p[0].x+p[1].x)/2;gesture.cy=(p[0].y+p[1].y)/2;
            }
            gesture.left=r.left+r.width/2;gesture.top=r.top+r.height/2;
        }
        stage.addEventListener('pointerdown',function (event) {
            if(event.button!==0 || points.size>=2) return;
            event.preventDefault();points.set(event.pointerId,{x:event.clientX,y:event.clientY});stage.setPointerCapture(event.pointerId);beginGesture();
        });
        stage.addEventListener('pointermove',function (event) {
            if(!gesture || !points.has(event.pointerId)) return;
            points.set(event.pointerId,{x:event.clientX,y:event.clientY});var p=Array.from(points.values());
            if(p.length>1 && gesture.distance>0) {
                zoom=clamp(gesture.zoom*Math.hypot(p[1].x-p[0].x,p[1].y-p[0].y)/gesture.distance,1,4);
                x=(p[0].x+p[1].x)/2-gesture.left-(gesture.cx-gesture.left-gesture.x)*zoom/gesture.zoom;
                y=(p[0].y+p[1].y)/2-gesture.top-(gesture.cy-gesture.top-gesture.y)*zoom/gesture.zoom;
            } else {x=gesture.x+event.clientX-gesture.cx;y=gesture.y+event.clientY-gesture.cy;}
            paint();
        });
        ['pointerup','pointercancel','lostpointercapture'].forEach(function (name) {stage.addEventListener(name,function (event) {points.delete(event.pointerId);beginGesture();});});
        stage.addEventListener('keydown',function (event) {
            if(['+','=','-'].includes(event.key)) {event.preventDefault();changeZoom(zoom+(event.key==='-'?-.5:.5));return;}
            if(!['ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(event.key) || zoom<=1) return;
            event.preventDefault();x+=event.key==='ArrowLeft'?24:event.key==='ArrowRight'?-24:0;y+=event.key==='ArrowUp'?24:event.key==='ArrowDown'?-24:0;paint();
        });
        image.addEventListener('load',function () {status.textContent='';paint();});
        image.addEventListener('error',function () {status.textContent='This photo could not be loaded. Please close the view and try again.';});
        window.addEventListener('resize',function () {if(dialog.open) paint();});
        return {dialog:dialog,close:close,open:function (source,returnFocus) {
            focus=returnFocus || source;zoom=1;x=y=0;gesture=null;points.clear();
            image.alt=source.getAttribute('alt') || 'Profile photo';dialog.setAttribute('aria-label',image.alt);
            status.textContent='Loading photo…';dialog.showModal();image.src=photoSource(source);paint();dialog.querySelector('.tq-viewer-close').focus({preventScroll:true});
        }};
    }
    function photoSource(source) {
        if(source.tagName.toLowerCase()==='svg') {
            var svg=source.cloneNode(true);svg.setAttribute('width','512');svg.setAttribute('height','512');
            return 'data:image/svg+xml;charset=utf-8,'+encodeURIComponent(new XMLSerializer().serializeToString(svg));
        }
        return source.getAttribute('src') ? (source.src || source.getAttribute('src')) : source.currentSrc;
    }
    function showPhoto(source,returnFocus) {
        if(!source || !photoSource(source)) return;
        if(!canDialog) {window.open(photoSource(source),'_blank','noopener,noreferrer');return;}
        if(!viewer) viewer=buildViewer();viewer.open(source,returnFocus);
    }
    function enhance(root) {
        var images=[];
        if(root.matches && root.matches(previewSelector)) images.push(root);
        if(root.querySelectorAll) images=images.concat(Array.from(root.querySelectorAll(previewSelector)));
        images.forEach(function (image) {
            // Filter icons and upload pickers retain their existing action.
            if(image.closest('button,a,label,.tq-image-dialog,[data-no-image-preview]')) return;
            image.removeAttribute('aria-hidden');
            image.setAttribute('data-image-preview','');image.tabIndex=0;image.setAttribute('role','button');
            image.setAttribute('aria-label','View '+(image.getAttribute('alt') || 'profile')+' photo');
            image.title='View photo';
        });
    }
    function openPreview(event) {
        var image=event.target.closest && event.target.closest('[data-image-preview]');
        if(!image || !image.getClientRects().length || !photoSource(image)) return;
        if(event.type==='keydown' && !['Enter',' '].includes(event.key)) return;
        event.preventDefault();event.stopImmediatePropagation();
        showPhoto(image);
    }
    document.addEventListener('change',cropChange,true);
    document.addEventListener('drop',function (event) {
        var zone=event.target.closest && event.target.closest('[data-image-crop-input]');
        if(!zone || !event.dataTransfer || !event.dataTransfer.files.length) return;
        var input=document.getElementById(zone.dataset.imageCropInput),file=event.dataTransfer.files[0];
        if(!canCrop(input,file)) return;
        event.preventDefault();event.stopImmediatePropagation();zone.classList.remove('drag-over');
        accepted.set(input,Array.from(input.files || []));
        setFiles(input,[file]);input.dispatchEvent(new Event('change',{bubbles:true}));
    },true);
    document.addEventListener('click',function (event) {
        var input=event.target;
        if(input instanceof HTMLInputElement && input.type==='file') accepted.set(input,Array.from(input.files || []));
    },true);
    document.addEventListener('click',openPreview,true);
    document.addEventListener('keydown',openPreview,true);
    document.addEventListener('submit',function (event) {if(crop && crop.active()) {event.preventDefault();event.stopImmediatePropagation();}},true);
    function init() {
        enhance(document.body);
        var observer=new MutationObserver(function (records) {
            records.forEach(function (record) {if(record.type==='attributes') enhance(record.target);else record.addedNodes.forEach(function (node) {if(node.nodeType===1) enhance(node);});});
        });
        observer.observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['src','data-vt-photo','data-vehicle-custom-photo']});
        window.addEventListener('pagehide',function () {observer.disconnect();if(crop) crop.cancel();if(viewer && viewer.dialog.open) viewer.close();});
        window.addEventListener('pageshow',function (event) {if(event.persisted) {enhance(document.body);observer.observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['src','data-vt-photo','data-vehicle-custom-photo']});}});
    }
    if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',init,{once:true});else init();
    window.TerminalImages=Object.freeze({open:showPhoto});
})(window,document);
