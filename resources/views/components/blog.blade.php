<section class="wrapper bg-light">
  <style type="text/css">
    .swiper-navigation{
      direction: ltr;
    }
  </style>
        <div class="blog-style">
          
          <div class="col-12 py-12 p-0">
            <div class="container p-0">
              
            
            <div class="col-12 p-3 row d-flex justify-content-center">
              <div class="col-12 text-center display-4 mb-12">
                   أحدث المقالات
              </div>
              @php
              $articles = \App\Models\Article::latest('id','ASC')->take(6)->get();
              @endphp
              @foreach($articles as $article)
              <div class="col-12 col-md-6 col-lg-4 p-2 my-lg-3 ">
                <a href="{{route('article.show',$article)}}">
                <div class="col-12 p-0 article-section position-relative d-flex align-items-center justify-content-center font-3 font-lg-5 rounded" style="height:200px;overflow: hidden;">
                  <div style="background-image:url('{{$article->main_image()}}');padding: 20px;height: 100%;background-size: cover;background-position: center;border-radius: 8px;color: #fff;width: 100%;position: absolute;"></div> 
                </div>
                <div style="z-index:1;color:#223badec;font-weight:bold;">{{$article->title}}</div>
              </a>
              </div>
              @endforeach
          
          
            </div>
            </div>
          </div>
          </div>
    </section>