'use client';

import {useState} from "react";
import {useToast} from "@/app/ui/toast/toast-provider";
import {ArrowUpTrayIcon} from "@heroicons/react/24/outline";
import clsx from "clsx";

export default function InputZipFile({text, onFileSelected}: {text: string, onFileSelected: CallableFunction}) {
    const [fileName, setFileName] = useState<string | null>(null);
    const [dragging, setDragging] = useState(false);
    const toast = useToast();

    const handleFile = (file: File | undefined) => {
        if (!file) {
            toast.showToast({
                type: "warning",
                title: "Fichier",
                message: "Merci de fournir un fichier"
            });
            return;
        }

        if (!file.name.endsWith(".zip")) {
            toast.showToast({
                type: "warning",
                title: "Fichier",
                message: "Merci de fournir un fichier zip (.zip)"
            });
            return;
        }

        setFileName(file.name || null);
        onFileSelected(file);
        return;
    }

    return (
        <label htmlFor="doc" key={`input-file-label-${text}`}
               className={clsx(
                   "flex items-center gap-0 rounded-xs border border-sky-400 border-dashed bg-sky-200 cursor-pointer",
                   {
                       "bg-sky-300": dragging,
                   }
               )}
               onDragOver={(e) => {e.preventDefault(); setDragging(true);}}
               onDragLeave={() => setDragging(false)}
               onDrop={(e) => {
                   e.preventDefault();
                   setDragging(false);
                   handleFile(e.dataTransfer?.files[0]);
               }}
        >
            <ArrowUpTrayIcon className="size-7 ml-1 mr-2" />
            <div className="">
                <h4 className="text-base font-semibold text-gray-700">{fileName || text}</h4>
                <span className="text-sm text-gray-500">(.zip)</span>
            </div>
            <input key={`input-file-input-${text}`} type="file" id="doc" name="doc" accept=".zip" hidden
                   onChange={(e) => handleFile(e.target.files?.[0])}/>
        </label>
    );
}