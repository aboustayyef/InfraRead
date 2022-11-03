<template>
    <div class="fixed inline-block px-8 py-2 transition duration-75 ease-out transform border border-gray-600 shadow-md translate-x-72 top-8 right-8"
            :class="{
                '-translate-x-72': notification.displayed == true,
                'bg-yellow-100': notification.kind == 'warning',
                'bg-blue-100': notification.kind == 'info',
                'bg-green-100': notification.kind == 'success',
            }">
            {{ notification.message }}
        </div>
</template>

<script>
export default {
    props: {
        notification: Object
    }
}
</script>
