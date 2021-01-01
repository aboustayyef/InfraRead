    <div class="flex justify-between items-start mt-6">
      <div>
        <div class="text-primary font-semibold text-xl tracking-wider">{{$title}}</div>
        <div class="text-gray-700">{{$description}}</div>
        <div class="text-gray-400">{{$category}}</div>
      </div>
      <div class="ml-4 text-gray-200 px-4 py-1 border rounded hover:text-white hover:bg-primary hover:border-none">
        <a href="{{$edit}}">edit</a>
      </div>
    </div>