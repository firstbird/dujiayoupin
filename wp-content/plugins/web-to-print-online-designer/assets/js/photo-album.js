(function($) {
    'use strict';
    
    var PhotoAlbum = {
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            $(document).on('click', '[data-type="photo-album"]', function() {
                PhotoAlbum.openAlbum();
            });
            
            // 监听文件上传
            $(document).on('change', '#nbd-photo-upload', function(e) {
                PhotoAlbum.handleFileUpload(e);
            });
        },
        
        openAlbum: function() {
            $('#nbd-photo-album').modal('show');
            this.loadPhotos();
        },
        
        loadPhotos: function() {
            var $scope = angular.element(document.getElementById('designer-controller')).scope();
            if (!$scope) {
                console.error('Angular scope not found');
                return;
            }
            
            $scope.isLoadingPhotos = true;
            
            $.ajax({
                url: NBDESIGNCONFIG.ajax_url,
                type: 'POST',
                data: {
                    action: 'nbdesigner_get_user_photos',
                    nonce: NBDESIGNCONFIG.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $scope.userPhotos = response.data.photos || [];
                    } else {
                        console.error('Failed to load photos:', response.data.message);
                        alert('加载相册失败：' + response.data.message);
                    }
                    $scope.isLoadingPhotos = false;
                    $scope.$apply();
                },
                error: function() {
                    console.error('AJAX error while loading photos');
                    alert('加载相册失败，请重试');
                    $scope.isLoadingPhotos = false;
                    $scope.$apply();
                }
            });
        },
        
        handleFileUpload: function(e) {
            var file = e.target.files[0];
            if (!file) return;
            
            var $scope = angular.element(document.getElementById('designer-controller')).scope();
            if (!$scope) {
                console.error('Angular scope not found');
                return;
            }
            
            var formData = new FormData();
            formData.append('action', 'nbdesigner_upload_user_photo');
            formData.append('nonce', NBDESIGNCONFIG.nonce);
            formData.append('file', file);
            
            $.ajax({
                url: NBDESIGNCONFIG.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $scope.userPhotos.unshift({
                            id: response.data.id,
                            url: response.data.url
                        });
                        $scope.$apply();
                    } else {
                        alert(response.data.message || '上传失败');
                    }
                },
                error: function() {
                    alert('上传失败，请重试');
                }
            });
        }
    };
    
    $(window).on('load', function() {
        PhotoAlbum.init();
    });
    
})(jQuery);