import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from '@/components/ui/sonner';
import { useActiveUser } from '@/lib/useActiveUser';
import type { UserProfile } from '@/types/user';

export function useProfileEdit(user: UserProfile) {
    const activeUser = useActiveUser();

    const nameValue = ref(
        user.name || activeUser.value?.name || '',
    );
    const usernameValue = ref(user.username || '');
    const bioValue = ref(user.bio || '');
    const phoneValue = ref(user.phone || '');
    const genderValue = ref(user.gender || '');
    const birthDateValue = ref(user.birthDate || '');
    const avatarValue = ref(
        user.avatar || activeUser.value?.photoURL || '',
    );

    const form = useForm({
        name: nameValue.value,
        username: usernameValue.value,
        bio: bioValue.value,
        phone: phoneValue.value,
        gender: genderValue.value,
        birth_date: birthDateValue.value,
        avatar: avatarValue.value,
    });

    const activeEditField = ref<string | null>(null);
    const tempEditValue = ref('');
    const copiedUserId = ref(false);

    const userAvatar = computed(() => {
        return avatarValue.value || activeUser.value?.photoURL || undefined;
    });

    const userInitial = computed(() => {
        return (nameValue.value || 'U').substring(0, 2).toUpperCase();
    });

    function copyUserId() {
        const id = user.userId ?? '';
        navigator.clipboard.writeText(id);
        copiedUserId.value = true;
        toast.success(`User ID ${id} berhasil disalin!`);
        setTimeout(() => {
            copiedUserId.value = false;
        }, 2000);
    }

    function openEditModal(field: string, currentValue: string) {
        activeEditField.value = field;
        tempEditValue.value = currentValue;
    }

    function saveField() {
        if (!activeEditField.value) {
            return;
        }

        if (activeEditField.value === 'name') {
            nameValue.value = tempEditValue.value || nameValue.value;
            form.name = nameValue.value;
        } else if (activeEditField.value === 'username') {
            usernameValue.value = tempEditValue.value;
            form.username = tempEditValue.value;
        } else if (activeEditField.value === 'bio') {
            bioValue.value = tempEditValue.value;
            form.bio = tempEditValue.value;
        } else if (activeEditField.value === 'phone') {
            phoneValue.value = tempEditValue.value;
            form.phone = tempEditValue.value;
        } else if (activeEditField.value === 'gender') {
            genderValue.value = tempEditValue.value;
            form.gender = tempEditValue.value;
        } else if (activeEditField.value === 'birthDate') {
            birthDateValue.value = tempEditValue.value;
            form.birth_date = tempEditValue.value;
        } else if (activeEditField.value === 'avatar') {
            avatarValue.value = tempEditValue.value;
            form.avatar = tempEditValue.value;
        }

        activeEditField.value = null;

        form.put('/profile/edit', {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Profil berhasil diperbarui!');
            },
        });
    }

    return {
        activeUser,
        nameValue,
        usernameValue,
        bioValue,
        phoneValue,
        genderValue,
        birthDateValue,
        avatarValue,
        form,
        activeEditField,
        tempEditValue,
        copiedUserId,
        userAvatar,
        userInitial,
        copyUserId,
        openEditModal,
        saveField,
    };
}