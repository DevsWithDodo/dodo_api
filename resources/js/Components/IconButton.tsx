import { useGlissadeButton } from "@enymo/glissade";
import { Clickable, ClickableProps } from "@enymo/react-clickable-router";
import classNames from "classnames";
import React from "react";
import Loader from "./Loader";

export default function IconButton({ 
    children,
    onClick: onClickProp,
    loading: loadingProp,
    disabled: disabledProp,
    submit,
    compact = false,
    className,
    ...props
}: {
    children: React.ReactNode,
    loading?: boolean;
    compact?: boolean;
    inline?: boolean;
} & Omit<ClickableProps, "children">) {

    const { disabled, loading, onClick} = useGlissadeButton({ 
        onClick: onClickProp, 
        disabled: disabledProp, 
        loading: loadingProp, 
        submit 
    });

    return (
        <Clickable
            className={classNames("flex items-center justify-center rounded-full [&_svg]:size-5 size-10 cursor-pointer bg-gray-50 [&_svg]:fill-black", className, {
                "!cursor-not-allowed": disabled,
            })}
            onClick={onClick} 
            disabled={disabled} 
            {...props}
        >
            {!loading && children}
            {loading && <Loader />}
        </Clickable>
    )
}