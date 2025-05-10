(function($) {
    'use strict';
    
    var PhotoAlbum = {
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            var self = this;
            $(document).on('click', '.nbd-open-photo-album', function(e) {
                e.preventDefault();
                self.openAlbum();
            });
            
            // 监听文件上传
            $(document).on('change', '#nbd-photo-upload', function(e) {
                self.handleFileUpload(e);
            });
            
            // 添加关闭按钮事件
            $(document).on('click', '.close', function() {
                PhotoAlbum.closeAlbum();
            });
            
            // 点击模态框外部关闭
            $(document).on('click', '#nbd-photo-album', function(e) {
                if (e.target === this) {
                    PhotoAlbum.closeAlbum();
                }
            });
        },
        
        openAlbum: function() {
            console.log('Opening photo album...');
            $('#nbd-photo-album-modal').modal('show');
            this.loadPhotos();
        },
        
        closeAlbum: function() {
            $('#nbd-photo-album').fadeOut(300);
            $('body').css('overflow', '');
        },
        
        loadPhotos: function() {
            var self = this;
            var nonce = $('#nbd-photo-album-nonce').val();
            var url = nbd_ajax.ajax_url;
            
            console.log('Loading photos...');
            console.log('AJAX URL:', url);
            console.log('Nonce:', nonce);

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    action: 'nbdesigner_get_user_photos',
                    nonce: nonce
                },
                beforeSend: function() {
                    $('#nbd-photo-grid').html('<div class="loading">加载中...</div>');
                },
                success: function(response) {
                    console.log('AJAX Response:', response);
                    
                    if (response.success) {
                        var photos = response.data.photos || [];
                        var html = '';
                        
                        if (photos.length === 0) {
                            html = '<div class="no-photos">暂无照片</div>';
                        } else {
                            photos.forEach(function(photo) {
                                html += '<div class="photo-item" data-id="' + photo.id + '">';
                                html += '<img src="' + photo.url + '" alt="用户照片">';
                                html += '<div class="photo-actions">';
                                html += '<button class="select-photo" data-id="' + photo.id + '">选择</button>';
                                html += '<button class="delete-photo" data-id="' + photo.id + '">删除</button>';
                                html += '</div>';
                                html += '</div>';
                            });
                        }
                        
                        $('#nbd-photo-grid').html(html);
                    } else {
                        console.error('Error loading photos:', response.data.message);
                        $('#nbd-photo-grid').html('<div class="error">' + (response.data.message || '加载失败') + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    $('#nbd-photo-grid').html('<div class="error">加载失败，请重试</div>');
                }
            });
        },
        
        handleFileUpload: function(e) {
            var self = this;
            var file = e.target.files[0];
            if (!file) return;
            
            var formData = new FormData();
            formData.append('action', 'nbdesigner_upload_user_photo');
            formData.append('nonce', $('#nbd-photo-album-nonce').val());
            formData.append('file', file);
            
            console.log('Uploading file:', file.name);
            
            $.ajax({
                url: nbd_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#nbd-photo-upload-area').addClass('uploading');
                },
                success: function(response) {
                    console.log('Upload Response:', response);
                    
                    if (response.success) {
                        self.loadPhotos();
                    } else {
                        console.error('Upload failed:', response.data.message);
                        alert(response.data.message || '上传失败');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Upload Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    alert('上传失败，请重试');
                },
                complete: function() {
                    $('#nbd-photo-upload-area').removeClass('uploading');
                    $('#nbd-photo-upload').val('');
                }
            });
        },
        
        deletePhoto: function(photoId) {
            var $scope = angular.element(document.getElementById('designer-controller')).scope();
            if (!$scope) {
                console.error('Angular scope not found');
                return;
            }
            
            NBDDataFactory.get('nbdesigner_delete_user_photo', {
                photo_id: photoId
            }, function(response) {
                if (response.success) {
                    var index = $scope.userPhotos.findIndex(function(p) {
                        return p.id === photoId;
                    });
                    if (index > -1) {
                        $scope.userPhotos.splice(index, 1);
                        $scope.$apply();
                    }
                } else {
                    alert(response.data.message);
                }
            });
        },
        
        selectPhoto: function(photo) {
            var $scope = angular.element(document.getElementById('designer-controller')).scope();
            if (!$scope) {
                console.error('Angular scope not found');
                return;
            }
            
            $scope.addImage(photo.url, false, true);
            PhotoAlbum.closeAlbum();
        }
    };
    
    $(document).ready(function() {
        PhotoAlbum.init();
    });
    
})(jQuery); 