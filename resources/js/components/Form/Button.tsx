import { cn } from "@/js/utils";
import { useGlissadeButton } from "@enymo/glissade";
import React from "react";
import Loader from "../Loader";


export default function Button({
    submit = false,
    loading: loadingProp,
    disabled: disabledProp,
    onClick: onClickProp,
    children,
    className,
}: {
    submit?: boolean,
    loading?: boolean,
    disabled?: boolean,
    onClick?: React.MouseEventHandler,
    children?: React.ReactNode,
    className?: string,
}) {
    const { disabled, loading, onClick } = useGlissadeButton({ 
        submit, 
        loading: loadingProp, 
        disabled: disabledProp, 
        onClick: onClickProp
    });

    return (
        <button
            type={submit ? "submit" : "button"}
            disabled={disabled}
            onClick={onClick} 
            className={cn(
                className,
                "relative flex items-center font-open font-medium h-[40px] px-[24px] rounded-[20px] shrink-0 bg-dodo-blue text-white",
            )}
        >
            <div className={loading ? "invisible" : ""}>{children}</div>
            {loading && (
                <Loader className={cn(
                    "absolute h-[20px] left-[calc(50%-10px)] top-[calc(50%-10px)] !fill-on-primary",
                )} />
            )}
        </button>
    )
}