import { cn } from "@/js/utils";
import { GlissadeInput, GlissadeInputProps, useGlissadeInput } from "@enymo/glissade";
import React from "react";

export interface InputProps extends GlissadeInputProps {
    label?: string;
    prefix?: React.ReactNode;
    suffix?: React.ReactNode;
    inputClassName?: string;
    error?: string;
}

export default function Input({
    label,
    className,
    prefix,
    suffix,
    inputClassName,
    disabled,
    name,
    options,
    error: errorProp,
    ...props
}: InputProps) {
    const { error } = useGlissadeInput({ name: name, disabled: disabled, error: errorProp });
    return (
        <label className="flex flex-col cursor-text">
            <div className={cn(
                "relative peer group shrink-0 flex items-center py-4 bg-gray-200 rounded-t-[4px]",
                className,
                disabled && "disabled",
                prefix ?  "pl-3 pr-4 gap-4" : "px-4",
                suffix ? "pr-3 pl-4 gap-4" : "px-4",
                label ? "pt-6" : "",
            )}>
                <span className={`
                    absolute bd-s top-2 
                    ${error !== undefined ? "text-red-400" : "text-gray-900 group-focus-within:!text-dodo-blue"}
                `}>
                    {label}{label && options?.required && <sup>*</sup>}
                </span>
                {prefix && <div className="shrink-0 size-[24px] flex items-center justify-center overflow-hidden fill-gray-800">
                    {prefix}
                </div>}
                <div className={`flex h-full flex-1 relative shrink-0 items-center`}>
                    <GlissadeInput
                        className={`bg-transparent outline-none grow text-gray-900 bd-l ${inputClassName ?? ""}`}
                        name={name}
                        disabled={disabled}
                        options={options}
                        {...props}
                    />
                </div>
                {suffix && <div className="shrink-0 size-[24px] flex items-center justify-center overflow-hidden fill-gray-800">
                    {suffix}
                </div>}
            </div>
            <div className={cn(
                "h-[1px]",
                error !== undefined ? "bg-red-400" : "bg-gray-900 peer-focus-within:bg-dodo-blue peer-focus-within:h-[2px]"
            )} />
            <div className="h-[1px] peer-focus-within:hidden" />
            {error && <div className="text-red-400 bd-s mt-1 ml-1">{error}</div>}
        </label>
    )
}