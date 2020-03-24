<div class="col-md-4">
    <div class="card mb-4 box-shadow">
        <img class="card-img-top" src="<?= $image ?>" alt="Card image cap">
        <div class="card-body">
            <p class="card-text"><?= $title ?></p>
            <div class="d-flex justify-content-between align-items-center">
                <div class="btn-group">
                    <a href="/users/<?= $id ?>" type="button" class="btn btn-sm btn-outline-secondary">View</a>
                    <a type="button" class="btn btn-sm btn-outline-secondary">Edit</a>
                </div>
                <small class="text-muted"><?= $subtitle ?></small>
            </div>
        </div>
    </div>
</div>