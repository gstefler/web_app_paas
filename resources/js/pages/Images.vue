<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { 
  Table, 
  TableBody, 
  TableCaption, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { 
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { AlertCircle, ArrowUpDown, Search, Upload, Trash2, Image as ImageIcon } from 'lucide-vue-next'
import { format } from 'date-fns'
import { toast } from '@/components/ui/toast'

type Image = {
    id: string
    name: string
    path: string
    created_at: string
}

interface Props {
    images: Image[]
}

const props = defineProps<Props>()
const selectedImage = ref<Image | null>(null)
const showImageDialog = ref(false)
const sortBy = ref<'name' | 'created_at'>('created_at')
const sortDirection = ref<'asc' | 'desc'>('desc')
const searchQuery = ref('')

const form = useForm({
    name: '',
    image: null as File | null
})

const fileInput = ref<HTMLInputElement | null>(null)

// Sort and filter images
const displayedImages = computed(() => {
  let filtered = props.images.filter(img => 
    img.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
  
  return filtered.sort((a, b) => {
    const aValue = sortBy.value === 'created_at' 
      ? new Date(a.created_at).getTime() 
      : a.name.toLowerCase()
    const bValue = sortBy.value === 'created_at' 
      ? new Date(b.created_at).getTime() 
      : b.name.toLowerCase()
    
    if (sortDirection.value === 'asc') {
      return aValue > bValue ? 1 : -1
    } else {
      return aValue < bValue ? 1 : -1
    }
  })
})

function toggleSort(column: 'name' | 'created_at') {
  if (sortBy.value === column) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = column
    sortDirection.value = 'asc'
  }
}

function deleteImage(id: string) {
  if (confirm('Are you sure you want to delete this image?')) {
    router.delete(route('images.destroy', id), {
      onSuccess: () => {
        toast({
          title: "Image deleted",
          description: "The image was successfully deleted",
        })
      }
    })
  }
}

function viewImage(image: Image) {
  selectedImage.value = image
  showImageDialog.value = true
}

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    form.image = target.files[0]
    form.name = form.image.name.split('.')[0]
  }
}

function uploadImage() {
  form.post(route('images.store'), {
    preserveScroll: true,
    forceFormData: true, // Ensure proper FormData handling
    onSuccess: () => {
      form.reset()
      if (fileInput.value) {
        fileInput.value.value = ''
      }
      toast({
        title: "Image uploaded",
        description: "Your image was successfully uploaded",
      })
    },
    onError: () => {
      toast({
        title: "Upload failed",
        description: "There was an error uploading your image",
        variant: "destructive",
      })
    }
  })
}

function formatDate(dateString: string) {
  return format(new Date(dateString), 'PPP')
}

function logout() {
  router.post(route('logout'), {}, {
    onError: () => {
      toast({
        title: "Logout failed",
        description: "There was an error logging out",
        variant: "destructive",
      })
    }
  })
}
</script>

<template>
  <div class="container mx-auto py-6 space-y-8">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold">Image Gallery</h1>
      <Button 
        variant="outline" 
        @click="logout" 
        class="flex items-center gap-2"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        Logout
      </Button>
    </div>
    
    <!-- Upload card -->
    <Card>
      <CardHeader>
        <CardTitle class="text-lg flex items-center gap-2">
          <Upload class="h-5 w-5" />
          Upload New Image
        </CardTitle>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="uploadImage" class="space-y-4">
          <div>
            <Label for="name">Image Name</Label>
            <Input id="name" v-model="form.name" placeholder="Enter image name" />
            <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
          </div>
          
          <div>
            <Label for="image">Image File</Label>
            <div class="mt-1 flex items-center">
              <input
                id="image"
                ref="fileInput"
                type="file"
                accept="image/*"
                @change="handleFileChange"
                class="hidden"
              />
              <Button
                type="button"
                variant="outline"
                class="flex items-center gap-2"
                @click="fileInput?.click()"
              >
                <Upload class="h-4 w-4" />
                Select Image
              </Button>
              <span class="ml-4" v-if="form.image">
                {{ form.image.name }}
              </span>
            </div>
            <div v-if="form.errors.image" class="text-red-500 text-sm mt-1">{{ form.errors.image }}</div>
          </div>
        </form>
      </CardContent>
      <CardFooter>
        <Button 
          type="button" 
          :disabled="!form.image || !form.name" 
          @click="uploadImage"
        >
          Upload Image
        </Button>
      </CardFooter>
    </Card>

    <!-- Search and filters -->
    <div class="flex items-center justify-between">
      <div class="relative">
        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          v-model="searchQuery"
          placeholder="Search images..."
          class="pl-10 w-[250px]"
        />
      </div>
      
      <div class="flex items-center gap-2">
        <span class="text-sm">Sort by:</span>
        <Select :value="sortBy" @update:value="sortBy = $event">
          <SelectTrigger class="w-[180px]">
            <SelectValue placeholder="Sort by" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="name">Name</SelectItem>
            <SelectItem value="created_at">Upload Date</SelectItem>
          </SelectContent>
        </Select>
        <Button variant="outline" size="sm" @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'">
          {{ sortDirection === 'asc' ? 'Ascending' : 'Descending' }}
        </Button>
      </div>
    </div>
    
    <!-- Images table -->
    <Table>
      <TableCaption>A list of your uploaded images</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[50px]">Preview</TableHead>
          <TableHead class="cursor-pointer" @click="toggleSort('name')">
            <div class="flex items-center">
              Name
              <ArrowUpDown class="ml-2 h-4 w-4" />
            </div>
          </TableHead>
          <TableHead class="cursor-pointer" @click="toggleSort('created_at')">
            <div class="flex items-center">
              Upload Date
              <ArrowUpDown class="ml-2 h-4 w-4" />
            </div>
          </TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="image in displayedImages" :key="image.id">
          <TableCell>
            <img 
              :src="image.path" 
              :alt="image.name" 
              class="h-10 w-10 object-cover rounded cursor-pointer"
              @click="viewImage(image)"
            />
          </TableCell>
          <TableCell>{{ image.name }}</TableCell>
          <TableCell>{{ formatDate(image.created_at) }}</TableCell>
          <TableCell>
            <div class="flex items-center gap-2">
              <Button variant="outline" size="sm" @click="viewImage(image)" class="flex items-center gap-1">
                <ImageIcon class="h-4 w-4" />
                View
              </Button>
              <Button variant="destructive" size="sm" @click="deleteImage(image.id)" class="flex items-center gap-1">
                <Trash2 class="h-4 w-4" />
                Delete
              </Button>
            </div>
          </TableCell>
        </TableRow>
        <TableRow v-if="displayedImages.length === 0">
          <TableCell colspan="4" class="text-center py-8">
            <div class="flex flex-col items-center text-muted-foreground">
              <AlertCircle class="h-12 w-12 mb-2" />
              <p>No images found</p>
            </div>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
    
    <!-- Image view dialog -->
    <Dialog :open="showImageDialog" @update:open="showImageDialog = $event">
      <DialogContent class="max-w-3xl">
        <DialogHeader>
          <DialogTitle>{{ selectedImage?.name }}</DialogTitle>
        </DialogHeader>
        <div class="mt-4">
          <img 
            v-if="selectedImage" 
            :src="selectedImage.path" 
            :alt="selectedImage.name" 
            class="w-full object-contain max-h-[70vh]" 
          />
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<style scoped>
.container {
  max-width: 1200px;
}
</style>
