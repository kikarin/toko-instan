<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Newspaper, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { toast } from '@/components/ui/sonner';

interface PostRow {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    body: string | null;
    cover_path: string | null;
    cover_url: string | null;
    meta_title: string | null;
    meta_description: string | null;
    blog_category_id: number | null;
    category: string | null;
    author: string | null;
    tags: string;
    is_published: boolean;
    published_at: string | null;
}

interface CategoryRow {
    id: number;
    name: string;
}

const props = defineProps<{ posts: PostRow[]; categories: CategoryRow[] }>();

const showForm = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    body: '',
    blog_category_id: null as number | null,
    tags: '',
    cover_path: '',
    meta_title: '',
    meta_description: '',
    is_published: false,
});

const categoryForm = useForm({ name: '' });

function xsrfHeaders(): Record<string, string> {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    const xsrfToken = match?.[1];

    return {
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': xsrfToken ? decodeURIComponent(xsrfToken) : '',
    };
}

async function uploadCover(e: Event) {
    const target = e.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) {
        return;
    }

    const data = new FormData();
    data.append('file', file);
    data.append('directory', 'blog');

    const response = await fetch('/uploads', { method: 'POST', headers: xsrfHeaders(), body: data });
    const result = await response.json();
    if (!response.ok) {
        toast.error(result.message ?? 'Upload gagal');
        return;
    }
    form.cover_path = result.path;
    toast.success('Cover diunggah');
    target.value = '';
}

function resetForm() {
    form.reset();
    editingId.value = null;
    showForm.value = false;
}

function edit(p: PostRow) {
    editingId.value = p.id;
    showForm.value = true;
    form.title = p.title;
    form.slug = p.slug;
    form.excerpt = p.excerpt ?? '';
    form.body = p.body ?? '';
    form.blog_category_id = p.blog_category_id;
    form.tags = p.tags;
    form.cover_path = p.cover_path ?? '';
    form.meta_title = p.meta_title ?? '';
    form.meta_description = p.meta_description ?? '';
    form.is_published = p.is_published;
}

function submit() {
    if (editingId.value) {
        form.put(`/blog/${editingId.value}`, {
            onSuccess: () => resetForm(),
        });
        return;
    }
    form.post('/blog', {
        onSuccess: () => resetForm(),
    });
}

function remove(p: PostRow) {
    router.delete(`/blog/${p.id}`);
}

function addCategory() {
    categoryForm.post('/blog/categories', {
        onSuccess: () => categoryForm.reset(),
    });
}
</script>

<template>
    <Head title="Blog" />
    <AppLayout title="Blog" activePage="Blog">
        <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4 sm:p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold">Blog CMS</h1>
                    <p class="text-sm text-muted-foreground">Artikel, kategori, tag, dan SEO meta.</p>
                </div>
                <Button class="gap-1" @click="showForm = !showForm">
                    <Plus class="h-4 w-4" /> Artikel baru
                </Button>
            </div>

            <Card>
                <CardContent class="flex flex-wrap items-end gap-3 p-4">
                    <div class="flex min-w-[200px] flex-1 flex-col gap-1.5">
                        <Label>Kategori baru</Label>
                        <Input v-model="categoryForm.name" placeholder="Tips belanja" />
                    </div>
                    <Button variant="outline" :disabled="categoryForm.processing" @click="addCategory">Tambah kategori</Button>
                </CardContent>
            </Card>

            <Card v-if="showForm">
                <CardContent class="grid gap-3 p-6 sm:grid-cols-2">
                    <div class="flex flex-col gap-1.5 sm:col-span-2">
                        <Label>Judul</Label>
                        <Input v-model="form.title" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Slug (opsional)</Label>
                        <Input v-model="form.slug" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Kategori</Label>
                        <select v-model="form.blog_category_id" class="h-10 rounded-md border bg-background px-3 text-sm">
                            <option :value="null">Tanpa kategori</option>
                            <option v-for="c in props.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col gap-1.5 sm:col-span-2">
                        <Label>Ringkasan</Label>
                        <Input v-model="form.excerpt" />
                    </div>
                    <div class="flex flex-col gap-1.5 sm:col-span-2">
                        <Label>Isi</Label>
                        <textarea v-model="form.body" rows="8" class="rounded-md border bg-background px-3 py-2 text-sm" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Tag (pisah koma)</Label>
                        <Input v-model="form.tags" placeholder="fashion, tips" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Cover</Label>
                        <input type="file" accept="image/*" @change="uploadCover" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Meta title</Label>
                        <Input v-model="form.meta_title" maxlength="70" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Meta description</Label>
                        <Input v-model="form.meta_description" maxlength="160" />
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input v-model="form.is_published" type="checkbox" />
                        Publikasikan
                    </label>
                    <div class="sm:col-span-2 flex gap-2">
                        <Button :disabled="form.processing" @click="submit">Simpan</Button>
                        <Button variant="outline" @click="resetForm">Batal</Button>
                    </div>
                </CardContent>
            </Card>

            <Card v-for="p in props.posts" :key="p.id">
                <CardContent class="flex flex-wrap items-center justify-between gap-3 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                            <Newspaper class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="font-bold">{{ p.title }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ p.author }} · {{ p.category || 'Tanpa kategori' }} ·
                                {{ p.is_published ? 'Terbit ' + (p.published_at ?? '') : 'Draft' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" size="sm" @click="edit(p)">Edit</Button>
                        <Button variant="ghost" size="sm" @click="remove(p)"><Trash2 class="h-4 w-4" /></Button>
                    </div>
                </CardContent>
            </Card>
            <p v-if="!props.posts.length" class="text-sm text-muted-foreground">Belum ada artikel.</p>
        </div>
    </AppLayout>
</template>
