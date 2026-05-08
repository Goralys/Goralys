import React, {ChangeEventHandler, FormEventHandler, MouseEventHandler, RefObject} from "react";

export function buildArray<T>(...items: (T | false | null | undefined)[]): T[] {
    return items.filter((item): item is T => Boolean(item));
}

export type UserRole = {
    role: "admin" | "teacher" | "student" | "none",
};

export const USER_ROLES = ["admin", "teacher", "student", "none"] as const satisfies ReadonlyArray<UserRole['role']>;

export type SubjectStatus = "not_submitted" | "submitted" | "rejected" | "approved";

export type Subject = {
    comment: string,
    hasDraft: boolean,
    lastRejected?: string,
    status: SubjectStatus,
    student: string,
    studentToken: string,
    subject: string,
    teacher: string,
    teacherToken: string,
    topic: string,
    interdisciplinary: boolean,
};

export type InputProps = {
    autocomplete?: string,
    disabled?: boolean,
    helper?: string,
    id: string,
    label: string,
    password?: boolean,
    required?: boolean,
    defaultValue?: string,
    onInput?: FormEventHandler<HTMLInputElement>,
};

export type TextAreaProps = {
    defaultValue?: string,
    disabled?: boolean,
    ref?:RefObject<HTMLTextAreaElement | null>
    helper?: string,
    id: string,
    label: string,
    onChangeAction?: ChangeEventHandler<HTMLTextAreaElement>,
};

export type SubjectTextAreaProps = {
    defaultValue?: string,
    disabled?: boolean,
    ref?:RefObject<HTMLTextAreaElement | null>
    helper?: string,
    id: string,
    label: string,
    maxLength?: number,
    onChangeAction?: ChangeEventHandler<HTMLTextAreaElement>,
    subjectData: Subject,
    animate: boolean,
};

export type SubjectInputMultilineProps = {
    helper?: string,
    id: string,
    label: string,
    onChangeAction?: ChangeEventHandler<HTMLTextAreaElement>,
    setIsInterdisciplinaryAction?: (v: boolean) => void,
    subjectData: Subject,
};

export type ButtonProps = {
    className?: string,
    text: string,
    type: "submit" | "button" | "reset",
    onClick?: MouseEventHandler<HTMLButtonElement>,
    color?: "sky" | "red" | "green" | "amber",
};

export type ToastProps = {
    type: "info" | "warning" | "error" | "success",
    title: string,
    message: string,
    visible: boolean,
};

export type Toast = {
    type: "error" | "warning" | "info" | "success",
    title: string,
    message: string,
    expires?: number,
};

export type AuthEvent = "unauthenticated" | "expired";

export type UserEvent = "login" | "logout" | "register";

export type ConfirmProps = {
    title: string,
    message: string,
    visible: boolean,
    onConfirmAction: () => void,
    onCancelAction: () => void,
};

export type ConfirmOptions = {
    title: string,
    message: string,
};

export type DraftModalProps = {
    visible: boolean,
    onCancelAction: () => void,
    onChooseDraftAction: (file: File | null) => void,
    onCloseModalAction: () => void,
}

export type DraftModalResult =
    | { type: "withDraft"; file: File | null }
    | { type: "withoutDraft" }
    | { type: "closed" }

export type ImportTopicsModalProps = {
    visible: boolean,
    onCancelAction: () => void,
    onImportTopicsAction: (file: File | null) => void,
    onCloseModalAction: () => void,
}

export type PasswordModalProps = {
    visible: boolean;
    onConfirmAction: (password: string) => void;
    onCancelAction: () => void;
    onCloseModalAction: () => void;
}

export type SubjectsSearchBarProps = {
    subjects: Subject[] | null,
    setCurrentSubjects: React.Dispatch<React.SetStateAction<Subject[] | null>>
}

export type CheckBoxProps = {
    id?: string,
    label: string,
    setValueAction: (v: boolean) => void,
    defaultValue: boolean,
    className?: string|null
    disabled?: boolean
}

export type UserData = {
    username: string,
    full_name: string,
    role: UserRole['role'],
    public_id: string,
}

export type User = {
    username: string,
    publicId: string,
    fullName: string,
    role: UserRole['role']
}

export type CookieValue = string | boolean | number | null | undefined

export const searchFields = {
    all: "Tout",
    student: "Élèves",
    teacher: "Professeur",
    topic: "Matière",
} as const;

export type SubjectsSearchField = keyof typeof searchFields

export type SubjectInputSkeletonProps = {
    showMeta?: boolean;
}

export type StudentCardSkeletonProps = {
    showButtons?: boolean;
}

export type TeacherCardSkeletonProps = {
    showButtons?: boolean;
}

export type UserCardSkeletonProps = {
    showReplaceTeacher?: boolean;
}
