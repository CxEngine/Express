<section class="jumbotron text-center">
    <div class="container">
        <h1 class="jumbotron-heading">Album example</h1>
        <p class="lead text-muted">Something short and leading about the collection below—its contents, the creator, etc. Make it short and sweet, but not too short so folks don't simply skip over it entirely.</p>
        <p>
            <a href="#" class="btn btn-primary my-2">Main call to action</a>
            <a href="#" class="btn btn-secondary my-2">Secondary action</a>
        </p>
    </div>
</section>

<div class="album py-5 bg-light">
    <div class="container">

        <div class="row">
            <?php foreach ($this->items as $item) { ?>
                <div class="col-md-4">
                    <div class="card mb-4 box-shadow">
                        <img class="card-img-top" src="<?= $item['image'] ?>" alt="Card image cap">
                        <div class="card-body">
                            <p class="card-text"><?=$item['title']?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="btn-group">
                                    <a href="/users/<?= $item['id'] ?>" type="button" class="btn btn-sm btn-outline-secondary">View</a>
                                    <a type="button" class="btn btn-sm btn-outline-secondary">Edit</a>
                                </div>
                                <small class="text-muted"><?=$item['subtitle']?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>