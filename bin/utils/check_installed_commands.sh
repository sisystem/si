NO_CMDS=""
for CMD in composer npm bin/dep doxygen rkt acbuild docker; do
    command -v $CMD >/dev/null 2>&1 || { NO_CMDS+=" ${CMD}"; }
done
if [[ -n $NO_CMDS ]]; then
    echo >&2 "Required but not installed:"
    echo >&2 "  ${NO_CMDS}"
    echo >&2 "Aborting."
    exit 1
fi
