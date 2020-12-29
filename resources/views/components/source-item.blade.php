    <div class="flex justify-between items-start max-w-5xl mt-4">
      <div>
        <div class="text-red-700 font-bold text-xl">{{$title}}</div>
        <div class="font-semibold">{{$description}}</div>
        <div class="text-gray-400">{{$category}}</div>
      </div>
      <div class="text-gray-500 px-4 py-1 border rounded hover:text-white hover:bg-red-700 hover:border-none">
        <a href="{{$edit}}">edit</a>
      </div>
    </div>