(function($) {
    'use strict';
    
    var PhotoAlbum = {
        currentTabType: null, // 添加当前 tab 类型记录
        ossNextToken: null, // 添加OSS分页标记
        
        init: function() {
            console.log('PhotoAlbum: 初始化');
            this.bindEvents();
            this.bindAngularEvents();
        },
        
        bindEvents: function() {
            var self = this;
            
            // 监听文件上传
            $(document).on('change', '#nbd-photo-upload', function(e) {
                console.log('PhotoAlbum: change 文件上传', e.target.files);
                if (e.target.files && e.target.files.length > 0) {
                    // 检查文件数量
                    if (e.target.files.length > 5) {
                        alert('一次最多只能上传5张图片');
                        $(this).val(''); // 清空选择
                        return;
                    }

                    var scope = angular.element(document.getElementById("designer-controller")).scope();
                    var uploadPromises = [];
                    
                    // 遍历所有选中的文件
                    Array.from(e.target.files).forEach(function(file) {
                        var promise = new Promise(function(resolve, reject) {
                            scope.saveCustomerFile(file, 'photo-album', function() {
                                resolve();
                            });
                        });
                        uploadPromises.push(promise);
                    });

                    // 等待所有文件上传完成
                    Promise.all(uploadPromises).then(function() {
                        self.loadPhotos();
                        $(e.target).val(''); // 清空选择
                    });
                }
            });

            // 监听拖拽上传
            $(document).on('dragover', '#nbd-photo-upload-area', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('uploading');
            }).on('dragleave', '#nbd-photo-upload-area', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('uploading');
            }).on('drop', '#nbd-photo-upload-area', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('uploading');
                
                var files = e.originalEvent.dataTransfer.files;
                if (files && files.length > 0) {
                    // 检查文件数量
                    if (files.length > 5) {
                        alert('一次最多只能上传5张图片');
                        return;
                    }

                    var scope = angular.element(document.getElementById("designer-controller")).scope();
                    var uploadPromises = [];
                    
                    // 遍历所有拖拽的文件
                    Array.from(files).forEach(function(file) {
                        if (file.type.startsWith('image/')) {
                            var promise = new Promise(function(resolve, reject) {
                                scope.saveCustomerFile(file, 'photo-album', function() {
                                    resolve();
                                });
                            });
                            uploadPromises.push(promise);
                        }
                    });

                    // 等待所有文件上传完成
                    Promise.all(uploadPromises).then(function() {
                        self.loadPhotos();
                    });
                }
            });
            
            // 添加选择照片事件
            $(document).on('click', '.photo-item img.select-photo', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var photoId = $(this).data('id');
                var photoUrl = $(this).attr('src');
                console.log('PhotoAlbum: click 选择照片', photoId, photoUrl);
                self.selectPhoto(photoId, photoUrl);
            });
            
            // 添加删除照片事件
            $(document).on('click', '.photo-delete', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var photoId = $(this).data('id');
                
                if (confirm('确定要删除这张照片吗？')) {
                    self.deletePhoto(photoId);
                }
            });

            // 监听模态窗口关闭按钮
            $(document).on('click', '#nbd-photo-album-modal .nbd-modal-close', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.closeModal();
            });

            // 监听清空相册按钮
            $(document).on('click', '#nbd-clear-album', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (confirm('确定要清空相册吗？此操作不可恢复！')) {
                    self.clearAlbum();
                }
            });

            // 监听模态窗口外部点击
            $(document).on('click', '#nbd-photo-album-modal', function(e) {
                if ($(e.target).is('#nbd-photo-album-modal')) {
                    self.closeModal();
                }
            });
        },

        bindAngularEvents: function() {
            var self = this;
            console.log('PhotoAlbum: 绑定Angular事件');
            
            // 监听相册按钮点击
            $(document).on('click', '[ng-click="openPhotoAlbum()"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // 通过按钮类名判断当前 tab 类型
                var $btn = $(this);
                if ($btn.hasClass('select-bg-btn')) {
                    self.currentTabType = 'background';
                } else if ($btn.hasClass('select-photo-btn')) {
                    self.currentTabType = 'photo';
                }
                
                console.log('PhotoAlbum: 相册按钮被点击，当前 tab 类型:', self.currentTabType);
                self.openAlbum();
            });
        },
        
        openAlbum: function() {
            console.log('PhotoAlbum: 打开相册');
            var $modal = $('#nbd-photo-album-modal');
            if ($modal.length) {
                // 根据 tab 类型设置提示文字
                var tipText = this.currentTabType === 'background' ? '选择加载图片作为背景' : '选择添加图片素材';
                $('.photo-tip').html(tipText);
                
                // 显示模态窗口
                $modal.addClass('show');
                
                // 加载照片
                this.loadPhotos();
            } else {
                console.error('PhotoAlbum: 找不到相册模态窗口');
            }
        },

        closeModal: function() {
            var $modal = $('#nbd-photo-album-modal');
            $modal.removeClass('show');
        },
        
        loadPhotos: function() {
            var self = this;
            console.log('PhotoAlbum: 加载照片');
            
            // 原有的相册加载逻辑
            $.ajax({
                url: nbd_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'nbdesigner_get_customer_files',
                    nonce: $('#nbd-photo-album-nonce').val()
                },
                success: function(response) {
                    console.log('PhotoAlbum: 加载照片成功', response);
                    var res = JSON.parse(response);
                    if (res.flag) {
                        self.renderPhotos(res.files);
                    } else {
                        $('#nbd-photo-grid').html('<div class="error">加载照片失败：' + response.data + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('PhotoAlbum: 加载照片失败', error);
                    $('#nbd-photo-grid').html('<div class="error">加载照片失败：' + error + '</div>');
                }
            });
        },
        
        renderPhotos: function(photos) {
            console.log('PhotoAlbum: 渲染照片', photos);
            if (!photos || photos.length === 0) {
                $('#nbd-photo-grid').html('<div class="no-photos">暂无照片</div>');
                return;
            }

            var html = '';
            photos.forEach(function(photo) {
                html += '<div class="photo-item" data-id="' + photo.id + '">';
                html += '<div class="photo-delete" data-id="' + photo.id + '"><i class="icon-nbd icon-nbd-close"></i></div>';
                html += '<img src="' + photo.url + '" alt="' + photo.name + '" class="select-photo" data-id="' + photo.id + '">';
                html += '</div>';
            });

            $('#nbd-photo-grid').html(html);
        },
        
        deletePhoto: function(photoId) {
            var self = this;
            console.log('PhotoAlbum: 删除照片', photoId);
            var scope = angular.element(document.getElementById("designer-controller")).scope();
            scope.toggleStageLoading();
            $.ajax({
                url: nbd_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'nbdesigner_delete_customer_files',
                    nonce: $('#nbd-photo-album-nonce').val(),
                    photo_id: photoId
                },
                success: function(response) {
                    console.log('PhotoAlbum: 删除成功', response);
                    var res = JSON.parse(response);
                    scope.toggleStageLoading();
                    if (res.flag) {
                        self.loadPhotos();
                    } else {
                        alert('删除失败：' + res.mes);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('PhotoAlbum: 删除失败', error);
                    scope.toggleStageLoading();
                    alert('删除失败：' + error);
                }
            });
        },
        
        selectPhoto: function(photoId, photoUrl) {
            var scope = angular.element(document.getElementById("designer-controller")).scope();
            
            console.log('selectPhoto currentTabType:', this.currentTabType);
            
            if (this.currentTabType === 'background') {
                // 如果是背景 tab，调用设置背景方法
                scope.setBackgroundInner(photoUrl);
            } else if (this.currentTabType === 'photo') {
                // 如果是图片 tab，调用添加图片方法
                scope.resource.addImageContext = 'manual';
                scope.addImage(photoUrl, false);
                
                // 添加到最近加载的图片列表
                this.addToRecentPhotos({
                    url: photoUrl,
                    name: photoUrl.split('/').pop(),
                    date: new Date().toLocaleDateString()
                });
            }
            
            // 关闭相册模态框
            this.closeModal();
        },

        // 添加新方法：添加到最近加载的图片列表
        addToRecentPhotos: function(photo) {
            console.log('PhotoAlbum: 添加到最近加载的图片', photo);
            
            // 获取Angular作用域
            var scope = angular.element(document.getElementById("designer-controller")).scope();
            if (!scope) {
                console.error('PhotoAlbum: 无法获取Angular作用域');
                return;
            }
            
            // 初始化recentPhotos数组
            if (!scope.recentPhotos) {
                scope.recentPhotos = [];
            }
            
            // 限制最近加载的图片数量为20张
            if (scope.recentPhotos.length >= 20) {
                scope.recentPhotos.pop();
            }
            
            // 添加到列表开头
            scope.recentPhotos.unshift(photo);
            
            // 触发Angular更新
            scope.$apply();
        },

        clearAlbum: function() {
            var self = this;
            console.log('PhotoAlbum: 清空相册');

            $.ajax({
                url: nbd_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'nbdesigner_clear_customer_album',
                    nonce: $('#nbd-photo-album-nonce').val()
                },
                success: function(response) {
                    console.log('PhotoAlbum: 清空相册成功', response);
                    var res = JSON.parse(response);
                    if (res.flag) {
                        self.loadPhotos();
                    } else {
                        alert('清空相册失败：' + res.mes);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('PhotoAlbum: 清空相册失败', error);
                    alert('清空相册失败：' + error);
                }
            });
        },
    };
    
    // 初始化
    $(document).ready(function() {
        PhotoAlbum.init();
    });
    
})(jQuery);