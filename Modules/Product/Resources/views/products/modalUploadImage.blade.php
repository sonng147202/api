<!-- Modal -->
<div id="modalUploadProductImage" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            {{Form::open(['method'=>'post', 'url'=>'/', 'id'=>'form_upload_product_mage', 'files' => true])}}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Upload ảnh sản phẩm</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Ảnh sản phẩm</label>
                    <input type="file" name="avatar" id="avatar" class="form-control dynamic_file_image"/>
                    <br/>
                    <button id="upload_image_modal" class="btn btn-primary ">Upload ảnh</button>
                </div>
            </div>
            {{Form::close()}}
        </div>
    </div>
</div>