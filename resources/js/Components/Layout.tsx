import React from "react";
import classNames from "classnames";
import "@/css/components/layout.scss";

type MainAxisAlignment = "start" | "center" | "end" | "space-between" | "space-around" | "space-evenly";
type CrossAxisAlignment = "start" | "center" | "end" | "stretch" | "baseline";

interface FlexProps {
    children: React.ReactNode;
    className?: string;
    gap?: string;
    mainAxisAlignment?: MainAxisAlignment;
    crossAxisAlignment?: CrossAxisAlignment;
    style?: React.CSSProperties;
    flex?: number;
}

export function Flex({
    type,
    children,
    className,
    gap,
    mainAxisAlignment,
    crossAxisAlignment,
    style,
    flex,
}: FlexProps & { type: "row" | "column" }) {
    return (
        <div className={classNames(type, className)} style={{
            gap,
            flex,
            justifyContent: mainAxisAlignment,
            alignItems: crossAxisAlignment,
            ...style,
        }}>
            {children}
        </div>
    );
}

export function Column(props: FlexProps) {
    return (
        <Flex {...props} type="column" />
    );
}

export function Row(props: FlexProps) {
    return (
        <Flex {...props} type="row" />
    );
}