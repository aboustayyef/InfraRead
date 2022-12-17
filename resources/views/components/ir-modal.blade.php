{{-- Delete Button --}}
<button class="cursor-pointer mt-12 ir_button warning flex group" type="button" id="turnOnModal">
    <svg class="text-primary h-6 -ml-2 mr-2 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
    </svg>
    Delete
</button>


{{-- modal --}}

{{-- dark background --}}
<div id="modal" class="hidden fixed inset-0 w-screen h-screen bg-gray-800 bg-opacity-80 flex flex-col justify-center items-center" tabindex="-1" role="dialog">

    {{-- modal itself --}}
    <div class="w-full sm:max-w-md mt-6 bg-white shadow-md overflow-hidden sm:rounded-lg h-auto" role="document">
      <div class="modal-content">
        {{-- header --}}
        <div class="modal-header flex justify-between p-4 items-center">
            <h4 class="modal-title text-2xl font-bold text-center text-primary">Are you sure?</h4>
            <button id="close" type="button" class="close text-3xl font-bold" aria-label="Close"><x-ui.close-button classes="h-8 w-8 hover:text-primary " /></button>
        </div>
        {{-- body --}}
        <div class="modal-body p-4">
          <p class="text-gray-500">{{$slot}}</p>
        </div>
        {{-- footer --}}
        <div class="modal-footer mt-4 px-4 py-6 border-t border-gray-200 bg-gray-100">
          <form method="POST" action="{{$formAction}}" class="flex justify-center">
              {{csrf_field()}}
              <input name="_method" type="hidden" value="DELETE">
              <div class="w-full flex justify-between">
                  <button id="cancel" type="button" class="flex-auto mr-4 px-4 py-2 border rounded-lg text-white bg-blue-700 hover:bg-blue-900">Cancel</button>
                  <button type="submit" class="flex-auto mr-4 px-4 py-2 border rounded-lg bg-primary hover:bg-red-900 text-white">Yes, delete permanently</button>
              </div>
          </form>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

{{-- Modal Behavior --}}
<script>
	document.addEventListener("DOMContentLoaded", function() {

		document.querySelector('#turnOnModal').addEventListener('click', (e)=> {
			document.querySelector('#modal').classList.remove('hidden');
		});

		document.querySelector('#close').addEventListener('click', (e)=> {
			document.querySelector('#modal').classList.add('hidden');
		});

		document.querySelector('#cancel').addEventListener('click', (e)=> {
			document.querySelector('#modal').classList.add('hidden');
		});
	});
</script>
