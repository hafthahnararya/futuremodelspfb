let selectedFiles = [];
let currentImageIndex = 0;
let isDragging = false;
let dragType = null;
let imageResizeData = {};
let currentPreviewIndex = 0;
let previewUrls = [];

function openAddPostModal() {
    console.log('Opening modal...');
    document.getElementById('addPostModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeAddPostModal() {
    document.getElementById('addPostModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    
    document.getElementById('postForm').reset();
    document.getElementById('fileInput').value = '';
    resetPreview();
    
    goToStep1();
    
    const nextBtn = document.getElementById('nextBtn');
    if (nextBtn) {
        nextBtn.classList.add('disabled');
        nextBtn.disabled = true;
    }
    
    const charCount = document.getElementById('charCount');
    if (charCount) {
        charCount.textContent = '0';
    }
    
    selectedFiles = [];
    currentImageIndex = 0;
    imageResizeData = {};
}

function resetPreview() {
    const preview = document.getElementById('imagePreview');
    if (preview) {
        preview.innerHTML = `
            <div class="upload-placeholder">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                    <path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M17 8L12 3L7 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 3V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <p>Select Image or Video</p>
            </div>
        `;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }
    
    const nextBtn = document.getElementById('nextBtn');
    if (nextBtn) {
        nextBtn.addEventListener('click', goToStep2);
    }
    
    const description = document.getElementById('description');
    if (description) {
        description.addEventListener('input', updateCharCount);
    }
    
    const postForm = document.getElementById('postForm');
    if (postForm) {
        postForm.addEventListener('submit', handleFormSubmit);
    }
    
    const modal = document.getElementById('addPostModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddPostModal();
            }
        });
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
    }
});
function handleFileSelect(e) {
    const files = Array.from(e.target.files);
    
    if (files.length === 0) return;
    
    files.forEach(file => {
        if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
            selectedFiles.push(file);
        }
    });
    
    if (selectedFiles.length > 0) {
        displayMediaPreview();
        updateImageControls();
        
        const nextBtn = document.getElementById('nextBtn');
        if (nextBtn) {
            nextBtn.classList.remove('disabled');
            nextBtn.disabled = false;
        }
    }
    
    e.target.value = '';
}

function generateFileId(file) {
    return file.name + '_' + file.size + '_' + file.lastModified;
}

function displayMediaPreview() {
    const preview = document.getElementById('imagePreview');
    if (!preview || selectedFiles.length === 0) return;
    
    const file = selectedFiles[currentImageIndex];
    
    const reader = new FileReader();
    reader.onload = function(e) {
        const isVideo = file.type.startsWith('video/');
        const fileId = generateFileId(file);
        
        preview.innerHTML = `
            <div class="media-container">
                ${isVideo ? 
                    `<div class="video-preview-container">
                        <video src="${e.target.result}" controls class="preview-video"></video>
                    </div>` :
                    `<div class="crop-wrapper" id="cropWrapper_${fileId}">
                        <img src="${e.target.result}" alt="Preview" class="crop-source-image" id="cropImage_${fileId}">
                        <div class="crop-overlay" id="cropOverlay_${fileId}">
                            <div class="crop-selection" id="cropSelection_${fileId}">
                                <div class="crop-handle corner nw"></div>
                                <div class="crop-handle corner ne"></div>
                                <div class="crop-handle corner sw"></div>
                                <div class="crop-handle corner se"></div>
                            </div>
                        </div>
                     </div>`
                }
                ${selectedFiles.length > 1 ? `
                    <div class="media-navigation">
                        <button type="button" onclick="changeMedia(-1)" class="nav-btn" ${currentImageIndex === 0 ? 'disabled' : ''}>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                        <span class="media-counter">${currentImageIndex + 1} / ${selectedFiles.length}</span>
                        <button type="button" onclick="changeMedia(1)" class="nav-btn" ${currentImageIndex === selectedFiles.length - 1 ? 'disabled' : ''}>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </button>
                    </div>
                ` : ''}
                <div class="current-media-badge">
                    ${file.name.length > 30 ? file.name.substring(0, 27) + '...' : file.name}
                    ${!isVideo ? `<br><span id="sizeDisplay_${fileId}">Loading...</span>` : ''}
                </div>
            </div>
        `;
        
        if (!isVideo) {
            const img = document.getElementById(`cropImage_${fileId}`);
            img.onload = function() {
                setTimeout(() => {
                    initializeCropData(fileId, this);
                    initializeCrop(fileId);
                }, 50);
            };
            
            if (img.complete) {
                setTimeout(() => {
                    initializeCropData(fileId, img);
                    initializeCrop(fileId);
                }, 50);
            }
        }
    };
    reader.readAsDataURL(file);
}

function initializeCropData(fileId, img) {
    const wrapper = document.getElementById(`cropWrapper_${fileId}`);
    const containerWidth = wrapper.offsetWidth;
    const containerHeight = 400; 
    
    const naturalWidth = img.naturalWidth;
    const naturalHeight = img.naturalHeight;
    const aspectRatio = naturalWidth / naturalHeight;
    
    let displayWidth, displayHeight;
    if (aspectRatio > containerWidth / containerHeight) {
        displayHeight = containerHeight;
        displayWidth = containerHeight * aspectRatio;
    } else {
        displayWidth = containerWidth;
        displayHeight = containerWidth / aspectRatio;
    }
    const offsetX = (containerWidth - displayWidth) / 2;
    const offsetY = (containerHeight - displayHeight) / 2;
    
    img.style.width = displayWidth + 'px';
    img.style.height = displayHeight + 'px';
    img.style.left = offsetX + 'px';
    img.style.top = offsetY + 'px';
    if (imageResizeData[fileId]) {
        imageResizeData[fileId].displayWidth = displayWidth;
        imageResizeData[fileId].displayHeight = displayHeight;
        imageResizeData[fileId].offsetX = offsetX;
        imageResizeData[fileId].offsetY = offsetY;
        imageResizeData[fileId].scale = naturalWidth / displayWidth;
        updateCropSelection(fileId);
        return;
    }
    const cropSize = Math.min(displayWidth, displayHeight);
    const cropX = (displayWidth - cropSize) / 2 + offsetX;
    const cropY = (displayHeight - cropSize) / 2 + offsetY;
    
    imageResizeData[fileId] = {
        naturalWidth: naturalWidth,
        naturalHeight: naturalHeight,
        displayWidth: displayWidth,
        displayHeight: displayHeight,
        offsetX: offsetX,
        offsetY: offsetY,
        cropX: cropX,
        cropY: cropY,
        cropWidth: cropSize,
        cropHeight: cropSize,
        scale: naturalWidth / displayWidth
    };
    
    updateCropSelection(fileId);
}

function updateCropSelection(fileId) {
    const selection = document.getElementById(`cropSelection_${fileId}`);
    const data = imageResizeData[fileId];
    
    if (selection && data) {
        selection.style.left = data.cropX + 'px';
        selection.style.top = data.cropY + 'px';
        selection.style.width = data.cropWidth + 'px';
        selection.style.height = data.cropHeight + 'px';
        
        updateSizeDisplay(fileId);
    }
}

function updateSizeDisplay(fileId) {
    const sizeDisplay = document.getElementById(`sizeDisplay_${fileId}`);
    const data = imageResizeData[fileId];
    
    if (sizeDisplay && data) {
        const realWidth = Math.round(data.cropWidth * data.scale);
        const realHeight = Math.round(data.cropHeight * data.scale);
        sizeDisplay.textContent = `Crop: ${realWidth} × ${realHeight}`;
    }
}

function initializeCrop(fileId) {
    const selection = document.getElementById(`cropSelection_${fileId}`);
    const overlay = document.getElementById(`cropOverlay_${fileId}`);
    const wrapper = document.getElementById(`cropWrapper_${fileId}`);
    
    if (!selection || !overlay || !wrapper) {
        console.error('Crop elements not found for:', fileId);
        return;
    }
    
    let startX, startY, startLeft, startTop, startWidth, startHeight;
    let activeHandle = null;
    const dragHandler = function(e) {
        if (e.target.classList.contains('crop-handle')) {
            return; 
        }
        
        e.preventDefault();
        e.stopPropagation();
        isDragging = true;
        dragType = 'move';
        
        const touch = e.touches ? e.touches[0] : e;
        startX = touch.clientX;
        startY = touch.clientY;
        startLeft = parseFloat(selection.style.left);
        startTop = parseFloat(selection.style.top);
        
        document.addEventListener('mousemove', doDrag);
        document.addEventListener('touchmove', doDrag);
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchend', stopDrag);
    };
    
    selection.addEventListener('mousedown', dragHandler);
    selection.addEventListener('touchstart', dragHandler);
    
    function doDrag(e) {
        if (!isDragging || dragType !== 'move') return;
        e.preventDefault();
        
        const touch = e.touches ? e.touches[0] : e;
        const deltaX = touch.clientX - startX;
        const deltaY = touch.clientY - startY;
        
        const data = imageResizeData[fileId];
        const cropWidth = parseFloat(selection.style.width);
        const cropHeight = parseFloat(selection.style.height);
        
        const minX = data.offsetX;
        const minY = data.offsetY;
        const maxX = data.offsetX + data.displayWidth - cropWidth;
        const maxY = data.offsetY + data.displayHeight - cropHeight;
        
        let newLeft = Math.max(minX, Math.min(maxX, startLeft + deltaX));
        let newTop = Math.max(minY, Math.min(maxY, startTop + deltaY));
        
        selection.style.left = newLeft + 'px';
        selection.style.top = newTop + 'px';
        
        data.cropX = newLeft;
        data.cropY = newTop;
        updateSizeDisplay(fileId);
    }
    
    function stopDrag() {
        isDragging = false;
        dragType = null;
        document.removeEventListener('mousemove', doDrag);
        document.removeEventListener('touchmove', doDrag);
        document.removeEventListener('mouseup', stopDrag);
        document.removeEventListener('touchend', stopDrag);
    }
    const handles = selection.querySelectorAll('.crop-handle');
    handles.forEach(handle => {
        const resizeHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            isDragging = true;
            dragType = 'resize';
            if (handle.classList.contains('nw')) activeHandle = 'nw';
            else if (handle.classList.contains('ne')) activeHandle = 'ne';
            else if (handle.classList.contains('sw')) activeHandle = 'sw';
            else if (handle.classList.contains('se')) activeHandle = 'se';
            
            const touch = e.touches ? e.touches[0] : e;
            startX = touch.clientX;
            startY = touch.clientY;
            startLeft = parseFloat(selection.style.left);
            startTop = parseFloat(selection.style.top);
            startWidth = parseFloat(selection.style.width);
            startHeight = parseFloat(selection.style.height);
            
            document.addEventListener('mousemove', doResize);
            document.addEventListener('touchmove', doResize);
            document.addEventListener('mouseup', stopResize);
            document.addEventListener('touchend', stopResize);
        };
        
        handle.addEventListener('mousedown', resizeHandler);
        handle.addEventListener('touchstart', resizeHandler);
    });
    
    function doResize(e) {
        if (!isDragging || dragType !== 'resize') return;
        e.preventDefault();
        
        const touch = e.touches ? e.touches[0] : e;
        const deltaX = touch.clientX - startX;
        const deltaY = touch.clientY - startY;
        
        const data = imageResizeData[fileId];
        let newLeft = startLeft;
        let newTop = startTop;
        let newWidth = startWidth;
        let newHeight = startHeight;
        
        const minSize = 100;
        const imageLeft = data.offsetX;
        const imageTop = data.offsetY;
        const imageRight = data.offsetX + data.displayWidth;
        const imageBottom = data.offsetY + data.displayHeight;
        
        switch(activeHandle) {
            case 'nw':
                newWidth = Math.max(minSize, startWidth - deltaX);
                newHeight = Math.max(minSize, startHeight - deltaY);
                newLeft = Math.max(imageLeft, Math.min(startLeft + startWidth - minSize, startLeft + deltaX));
                newTop = Math.max(imageTop, Math.min(startTop + startHeight - minSize, startTop + deltaY));
                newWidth = startWidth - (newLeft - startLeft);
                newHeight = startHeight - (newTop - startTop);
                break;
            case 'ne':
                newWidth = Math.max(minSize, Math.min(imageRight - startLeft, startWidth + deltaX));
                newHeight = Math.max(minSize, startHeight - deltaY);
                newTop = Math.max(imageTop, Math.min(startTop + startHeight - minSize, startTop + deltaY));
                newHeight = startHeight - (newTop - startTop);
                break;
            case 'sw':
                newWidth = Math.max(minSize, startWidth - deltaX);
                newHeight = Math.max(minSize, Math.min(imageBottom - startTop, startHeight + deltaY));
                newLeft = Math.max(imageLeft, Math.min(startLeft + startWidth - minSize, startLeft + deltaX));
                newWidth = startWidth - (newLeft - startLeft);
                break;
            case 'se':
                newWidth = Math.max(minSize, Math.min(imageRight - startLeft, startWidth + deltaX));
                newHeight = Math.max(minSize, Math.min(imageBottom - startTop, startHeight + deltaY));
                break;
        }
        
        selection.style.left = newLeft + 'px';
        selection.style.top = newTop + 'px';
        selection.style.width = newWidth + 'px';
        selection.style.height = newHeight + 'px';
        
        data.cropX = newLeft;
        data.cropY = newTop;
        data.cropWidth = newWidth;
        data.cropHeight = newHeight;
        updateSizeDisplay(fileId);
    }
    
    function stopResize() {
        isDragging = false;
        dragType = null;
        activeHandle = null;
        document.removeEventListener('mousemove', doResize);
        document.removeEventListener('touchmove', doResize);
        document.removeEventListener('mouseup', stopResize);
        document.removeEventListener('touchend', stopResize);
    }
}

function updateImageControls() {
    const imageControls = document.getElementById('imageControls');
    const removeCurrentBtn = document.getElementById('removeCurrentBtn');
    
    if (!imageControls || !removeCurrentBtn) return;
    
    const currentFile = selectedFiles[currentImageIndex];
    if (currentFile && currentFile.type.startsWith('image/')) {
        imageControls.style.display = 'flex';
    } else {
        imageControls.style.display = 'none';
    }
    
    removeCurrentBtn.style.display = selectedFiles.length > 1 ? 'block' : 'none';
}

function changeMedia(direction) {
    saveCropData();
    
    currentImageIndex += direction;
    currentImageIndex = Math.max(0, Math.min(currentImageIndex, selectedFiles.length - 1));
    displayMediaPreview();
    updateImageControls();
}

function saveCropData() {
    const currentFile = selectedFiles[currentImageIndex];
    if (!currentFile || !currentFile.type.startsWith('image/')) return;
    
    const fileId = generateFileId(currentFile);
    const selection = document.getElementById(`cropSelection_${fileId}`);
    
    if (selection && imageResizeData[fileId]) {
        console.log('Crop data saved for:', fileId);
    }
}

function removeCurrentMedia() {
    if (selectedFiles.length === 0) return;
    
    const currentFile = selectedFiles[currentImageIndex];
    if (currentFile && currentFile.type.startsWith('image/')) {
        const fileId = generateFileId(currentFile);
        delete imageResizeData[fileId];
    }
    
    selectedFiles.splice(currentImageIndex, 1);
    
    if (selectedFiles.length === 0) {
        resetPreview();
        const nextBtn = document.getElementById('nextBtn');
        if (nextBtn) {
            nextBtn.classList.add('disabled');
            nextBtn.disabled = true;
        }
        currentImageIndex = 0;
        const imageControls = document.getElementById('imageControls');
        if (imageControls) {
            imageControls.style.display = 'none';
        }
    } else {
        if (currentImageIndex >= selectedFiles.length) {
            currentImageIndex = selectedFiles.length - 1;
        }
        displayMediaPreview();
        updateImageControls();
    }
}

function removeAllMedia() {
    imageResizeData = {};
    selectedFiles = [];
    currentImageIndex = 0;
    resetPreview();
    
    const imageControls = document.getElementById('imageControls');
    if (imageControls) {
        imageControls.style.display = 'none';
    }
    
    const nextBtn = document.getElementById('nextBtn');
    if (nextBtn) {
        nextBtn.classList.add('disabled');
        nextBtn.disabled = true;
    }
}
function goToStep2() {
    if (selectedFiles.length > 0) {
        saveCropData();
        
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        
        if (step1 && step2) {
            step1.classList.remove('active');
            step2.classList.add('active');
            updateFinalPreview();
        }
    }
}

function goToStep1() {
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    
    if (step1 && step2) {
        step2.classList.remove('active');
        step1.classList.add('active');
    }
}


async function updateFinalPreview() {
    const container = document.querySelector('.post-preview');
    if (!container || selectedFiles.length === 0) return;
    
    container.innerHTML = '<div class="loading-preview">Processing media...</div>';
    previewUrls = [];
    
    try {
        for (let i = 0; i < selectedFiles.length; i++) {
            const file = selectedFiles[i];
            const fileId = generateFileId(file);
            
            if (file.type.startsWith('video/')) {
                const videoUrl = await fileToDataURL(file);
                previewUrls.push({ type: 'video', url: videoUrl });
            } else {
                const cropData = imageResizeData[fileId];
                if (cropData) {
                    const croppedUrl = await generateCroppedPreview(file, cropData);
                    previewUrls.push({ type: 'image', url: croppedUrl });
                } else {
                    const imageUrl = await fileToDataURL(file);
                    previewUrls.push({ type: 'image', url: imageUrl });
                }
            }
        }
        
        currentPreviewIndex = 0;
        displayPreviewSlide();
    } catch (error) {
        console.error('Error generating previews:', error);
        container.innerHTML = '<div class="error-preview">Error loading preview</div>';
    }
}
function displayPreviewSlide() {
    const container = document.querySelector('.post-preview');
    if (!container || previewUrls.length === 0) return;
    
    const currentMedia = previewUrls[currentPreviewIndex];
    
    container.innerHTML = `
        <div class="preview-slideshow">
            ${currentMedia.type === 'video' ? 
                `<video src="${currentMedia.url}" controls class="final-preview-video"></video>` :
                `<img src="${currentMedia.url}" alt="Preview ${currentPreviewIndex + 1}" class="final-preview-img">`
            }
            ${previewUrls.length > 1 ? `
                <div class="preview-navigation">
                    <button type="button" onclick="changePreviewSlide(-1)" class="preview-nav-btn" ${currentPreviewIndex === 0 ? 'disabled' : ''}>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                    <div class="preview-dots">
                        ${previewUrls.map((_, index) => `
                            <span class="preview-dot ${index === currentPreviewIndex ? 'active' : ''}" onclick="goToPreviewSlide(${index})"></span>
                        `).join('')}
                    </div>
                    <button type="button" onclick="changePreviewSlide(1)" class="preview-nav-btn" ${currentPreviewIndex === previewUrls.length - 1 ? 'disabled' : ''}>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
                <div class="preview-counter">${currentPreviewIndex + 1} / ${previewUrls.length}</div>
            ` : ''}
        </div>
    `;
}

function changePreviewSlide(direction) {
    currentPreviewIndex += direction;
    currentPreviewIndex = Math.max(0, Math.min(currentPreviewIndex, previewUrls.length - 1));
    displayPreviewSlide();
}

function goToPreviewSlide(index) {
    currentPreviewIndex = index;
    displayPreviewSlide();
}

function fileToDataURL(file) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            resolve(e.target.result);
        };
        reader.readAsDataURL(file);
    });
}

function generateCroppedPreview(file, cropData) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                
                const scaleX = cropData.naturalWidth / cropData.displayWidth;
                const scaleY = cropData.naturalHeight / cropData.displayHeight;
                
                const cropXReal = (cropData.cropX - cropData.offsetX) * scaleX;
                const cropYReal = (cropData.cropY - cropData.offsetY) * scaleY;
                const cropWidthReal = cropData.cropWidth * scaleX;
                const cropHeightReal = cropData.cropHeight * scaleY;
                
                canvas.width = cropWidthReal;
                canvas.height = cropHeightReal;
                
                const ctx = canvas.getContext('2d');
                ctx.drawImage(
                    img,
                    cropXReal, cropYReal, cropWidthReal, cropHeightReal,
                    0, 0, cropWidthReal, cropHeightReal
                );
                
                resolve(canvas.toDataURL(file.type, 0.95));
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function updateCharCount() {
    const description = document.getElementById('description');
    const charCount = document.getElementById('charCount');
    if (description && charCount) {
        const count = description.value.length;
        charCount.textContent = count;
    }
}

function handleFormSubmit(e) {
    e.preventDefault();
    
    const title = document.getElementById('title').value.trim();
    if (!title) {
        alert('Please enter a title');
        return;
    }
    
    if (selectedFiles.length === 0) {
        alert('Please select at least one image or video');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'create_post');
    formData.append('title', title);
    formData.append('description', document.getElementById('description').value);
    const processFiles = async () => {
        for (let i = 0; i < selectedFiles.length; i++) {
            const file = selectedFiles[i];
            const fileId = generateFileId(file);
            
            if (file.type.startsWith('image/') && imageResizeData[fileId]) {
                try {
                    const croppedBlob = await cropImage(file, imageResizeData[fileId]);
                    formData.append('post_media[]', croppedBlob, file.name);
                } catch (error) {
                    console.error('Error cropping image:', error);
                    formData.append('post_media[]', file);
                }
            } else {
                formData.append('post_media[]', file);
            }
        }
        
        return formData;
    };
    
    const postBtn = document.querySelector('.post-btn');
    const originalText = postBtn.textContent;
    postBtn.textContent = 'Posting...';
    postBtn.disabled = true;
    
    processFiles().then(processedFormData => {
        fetch('add_post.php', {
            method: 'POST',
            body: processedFormData
        })
        .then(response => response.text())
        .then(data => {
            closeAddPostModal();
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
        })
        .finally(() => {
            postBtn.textContent = originalText;
            postBtn.disabled = false;
        });
    });
}
function cropImage(file, cropData) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                
                const scaleX = cropData.naturalWidth / cropData.displayWidth;
                const scaleY = cropData.naturalHeight / cropData.displayHeight;
                
                const cropXReal = (cropData.cropX - cropData.offsetX) * scaleX;
                const cropYReal = (cropData.cropY - cropData.offsetY) * scaleY;
                const cropWidthReal = cropData.cropWidth * scaleX;
                const cropHeightReal = cropData.cropHeight * scaleY;
                
                canvas.width = cropWidthReal;
                canvas.height = cropHeightReal;
                
                const ctx = canvas.getContext('2d');
                ctx.drawImage(
                    img,
                    cropXReal, cropYReal, cropWidthReal, cropHeightReal,
                    0, 0, cropWidthReal, cropHeightReal
                );
                
                canvas.toBlob((blob) => {
                    resolve(blob);
                }, file.type, 0.95);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function resetImageSize() {
    const currentFile = selectedFiles[currentImageIndex];
    if (!currentFile || !currentFile.type.startsWith('image/')) return;
    
    const fileId = generateFileId(currentFile);
    initializeCropData(fileId, document.getElementById(`cropImage_${fileId}`));
    updateCropSelection(fileId);
}
