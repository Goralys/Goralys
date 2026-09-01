import { ReactElement } from "react";

interface Props {
    title: string;
}

export default function CardTitle({ title }: Props): ReactElement {
    return (
        <>
            <p className="text-lg">{title}</p>
            <span className="w-full bg-sky-300 h-px -mt-3 mb-1.5" />
        </>
    );
}
